# WHMCS-Mollie-Payment-System
Mollie Integration für WHMCS v9

> Basiert auf dem MIT-lizenzierten Projekt [0100Dev/WHMCS-Mollie-Payments](https://github.com/0100Dev/WHMCS-Mollie-Payments), für WHMCS 9 / PHP 8.2+ neu aufgebaut.

## Anforderungen
- WHMCS 9.x (WHMCS 8.x mit PHP 8.2+ sollte ebenfalls funktionieren)
- PHP 8.2 oder höher mit der Erweiterung `curl`
- Ein [Mollie](https://www.mollie.com/)-Konto mit API-Key

Das Modul hat **keine Composer-Abhängigkeiten** mehr. Es spricht die Mollie API v2 direkt über cURL an und kann deshalb nicht mit den Bibliotheken kollidieren, die WHMCS selbst mitbringt (Guzzle, PSR usw.).

## Installation
1. Die `WHMCS-Mollie-Payments.zip` von der [Releases-Seite](https://github.com/ScriptVortexDE/WHMCS-Mollie-Payment-System/releases) herunterladen, alternativ den Ordner `src` aus dem Repository verwenden.
2. Den **Inhalt** des Ordners `src` nach `/modules/gateways/` deiner WHMCS-Installation hochladen.
3. In WHMCS unter *System Settings > Payment Gateways* die gewünschten Mollie-Zahlungsarten aktivieren.
4. Bei jeder aktivierten Zahlungsart den **Live API key** eintragen (optional den **Test API key** und *Test mode*).

Die Zahlungsarten müssen außerdem im Mollie-Dashboard freigeschaltet sein.

## Zahlungsarten
| Modul | Mollie-Methode |
|---|---|
| Mollie Checkout | Mollie-Bezahlseite mit allen im Mollie-Konto aktiven Methoden |
| Alma, Klarna, in3 | Pay later (Rechnungsadresse und Rechnungsposition werden automatisch übergeben) |
| Apple Pay, Credit Card, PayPal | Karten und Wallets |
| Bancontact, Belfius, KBC/CBC, iDEAL, EPS, Przelewy24, BLIK, MyBank, Trustly, Pay by Bank | Online-Banking |
| Bank Transfer | Überweisung (Bankdaten werden nach dem Checkout angezeigt) |
| TWINT, Satispay, Bancomat Pay, Bizum, MB WAY, Multibanco, MobilePay, Vipps, Swish, Wero | Lokale Zahlungsarten |
| Gift Card, paysafecard | Gutscheine und Prepaid |

## Funktionen
- Webhook-Verarbeitung **und** direkte Statusprüfung, wenn der Kunde zurückkehrt (die Rechnung wird sofort als bezahlt markiert)
- Schutz gegen doppelte Verbuchung, wenn Webhook und Rückleitung gleichzeitig eintreffen
- Bei einem Neuladen der Seite wird die offene Mollie-Zahlung wiederverwendet, statt eine neue anzulegen
- **Rückerstattungen** direkt aus WHMCS (*Invoice > Refund*)
- **Testmodus** mit separatem Test-API-Key
- Mollie-Bezahlseite in der Sprache des Kunden (Sprachdateien: Englisch, Deutsch, Niederländisch)
- API-Aufrufe erscheinen im WHMCS-Modul-Log (*Utilities > Logs > Module Log*), der API-Key wird dort maskiert

## Update vom 0100Dev-Modul
- Die Tabelle `gateway_mollie` wird weiterverwendet und automatisch erweitert. Offene Zahlungen bleiben gültig.
- **Giropay** und **SOFORT** wurden von Mollie eingestellt. Deaktiviere sie in WHMCS **vor** dem Update und lösche danach `molliegiropay_devapp.php` und `molliesofort_devapp.php`.
- Die alten Dateien `modules/gateways/mollie/mollie.php` und `modules/gateways/mollie/vendor/` werden nicht mehr benötigt und können gelöscht werden.

## Lizenz
MIT, siehe [LICENSE](LICENSE).
