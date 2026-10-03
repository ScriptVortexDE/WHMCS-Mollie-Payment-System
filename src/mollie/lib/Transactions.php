<?php

namespace ScriptVortex\WhmcsMollie;

use WHMCS\Database\Capsule;

/**
 * Storage for the link between WHMCS invoices and Mollie payments.
 *
 * Uses the same table as the original 0100Dev module, so existing installations
 * keep working; missing columns are added automatically.
 */
class Transactions
{
    public const TABLE = 'gateway_mollie';

    private static bool $schemaChecked = false;

    public static function ensureSchema(): void
    {
        if (self::$schemaChecked) {
            return;
        }

        $schema = Capsule::schema();

        if (!$schema->hasTable(self::TABLE)) {
            $schema->create(self::TABLE, function ($table) {
                $table->increments('id');
                $table->string('paymentid', 64)->nullable()->unique();
                $table->decimal('amount', 16, 2);
                $table->integer('currencyid');
                $table->string('ip', 50);
                $table->integer('userid');
                $table->integer('invoiceid')->index();
                $table->enum('status', ['open', 'paid', 'closed'])->default('open');
                $table->string('method', 25)->default('');
                $table->string('gateway', 64)->nullable();
                $table->boolean('testmode')->default(false);
                $table->timestamp('created')->useCurrent();
                $table->timestamp('updated')->nullable();
            });
        } else {
            if (!$schema->hasColumn(self::TABLE, 'gateway')) {
                $schema->table(self::TABLE, function ($table) {
                    $table->string('gateway', 64)->nullable();
                });
            }

            if (!$schema->hasColumn(self::TABLE, 'testmode')) {
                $schema->table(self::TABLE, function ($table) {
                    $table->boolean('testmode')->default(false);
                });
            }
        }

        self::$schemaChecked = true;
    }

    public static function create(array $data): int
    {
        return (int) Capsule::table(self::TABLE)->insertGetId($data);
    }

    public static function find(int $id): ?object
    {
        return Capsule::table(self::TABLE)->where('id', $id)->first();
    }

    public static function findByPaymentId(string $paymentId): ?object
    {
        return Capsule::table(self::TABLE)->where('paymentid', $paymentId)->first();
    }

    /**
     * Finds a recently created, still open payment for the same invoice, so a page
     * refresh sends the customer back to the same Mollie checkout instead of
     * creating a new payment every time.
     */
    public static function findReusable(int $invoiceId, string $gateway, float $amount, int $currencyId, bool $testMode): ?object
    {
        return Capsule::table(self::TABLE)
            ->where('invoiceid', $invoiceId)
            ->where('gateway', $gateway)
            ->where('currencyid', $currencyId)
            ->where('testmode', $testMode ? 1 : 0)
            ->where('status', 'open')
            ->whereNotNull('paymentid')
            ->where('created', '>=', date('Y-m-d H:i:s', time() - 1800))
            ->orderBy('id', 'desc')
            ->get()
            ->first(function ($row) use ($amount) {
                return abs((float) $row->amount - $amount) < 0.005;
            });
    }

    public static function setPaymentId(int $id, string $paymentId): void
    {
        Capsule::table(self::TABLE)->where('id', $id)->update(['paymentid' => $paymentId]);
    }

    /**
     * Atomically moves a transaction from "open" to the given status.
     *
     * Returns true only for the single request that performed the change, which
     * prevents an invoice from being paid twice when the webhook and the return
     * page process the same payment at the same moment.
     */
    public static function transition(int $id, string $status): bool
    {
        return Capsule::table(self::TABLE)
            ->where('id', $id)
            ->where('status', 'open')
            ->update(['status' => $status, 'updated' => date('Y-m-d H:i:s')]) === 1;
    }

    /**
     * Rolls a transaction back to "open", e.g. when booking the payment in WHMCS
     * failed, so the next webhook call can retry it.
     */
    public static function reopen(int $id): void
    {
        Capsule::table(self::TABLE)->where('id', $id)->update(['status' => 'open', 'updated' => date('Y-m-d H:i:s')]);
    }
}
