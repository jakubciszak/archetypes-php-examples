
# Accounting Archetype w Programie Lojalnościowym (E-commerce, multi-market)

Ten dokument streszcza rozmowę i prezentuje **kompletny model programu lojalnościowego** oparty na wzorcu *Accounting / Ledger Archetype* (inspirowanym m.in. Martin Fowler / Enterprise Patterns).  
Model jest zaprojektowany pod:
- e-commerce (np. LPP),
- wiele rynków (różne przeliczniki, TTL punktów),
- punkty oczekujące (pending),
- zwroty,
- złożone promocje,
- pełny audyt i odtwarzalność.

---

## 1. Główna idea

Logika lojalnościowa jest **oddzielona od mechaniki księgowej**.

- Domeny (zakupy, zwroty, promocje) emitują **Transaction**
- Silnik księgowy interpretuje je przez **PostingRule**
- Każda reguła tworzy **Entry** w jednym lub wielu **Account**
- Salda są wynikiem sumowania wpisów (ledger)

> Zdarzenie -> Reguły -> Wpisy -> Konta

---

## 2. Drzewo kont (per klient)

```
LoyaltyAccount (root)
├── PendingFromPurchases
├── PendingFromPromos
├── ActivePoints
├── SpentPoints
├── ExpiredPoints
├── ReversedPoints
└── AdjustmentPoints
```

Znaczenie:
- **Pending** – punkty oczekujące na aktywację (okres zwrotów)
- **Active** – dostępne do użycia
- **Spent** – wydane
- **Expired** – wygasłe
- **Reversed** – cofnięte (zwroty)
- **Adjustment** – korekty manualne

---

## 3. Transaction (zdarzenia biznesowe)

Przykładowe typy:
- PurchaseCompleted
- OrderDelivered
- ReturnAccepted
- PendingMatured
- PointsRedeemed
- PromoProductBought
- AppCheckinStreakCompleted
- FastPickupConfirmed

Transaction = fakt biznesowy, **nie zapis księgowy**.

---

## 4. Entry (wpis księgowy)

Pojedyncza linia w ledgerze:
- amount: Points (może być ujemne)
- whenCharged – kiedy powstał obowiązek
- whenBooked – kiedy zaksięgowano
- accountId – gdzie zapisano
- transactionId – źródło
- subjectRef – np. orderId, lineId
- attributes – market, campaign, rule

Immutable + audytowalne.

---

## 5. PostingRule (serce systemu)

PostingRule definiuje:
- **trigger** – kiedy reaguje
- **condition** – warunki (market, brand, kanał)
- **method** – algorytm liczenia punktów
- **outputs** – mapowanie wyników na konta

Jedna reguła może tworzyć **wiele wpisów** (keyed output).

---

## 6. AccountingPractice (polityka rynku)

AccountingPractice = zestaw reguł dla:
- programu
- rynku
- opcjonalnie brandu

Pozwala różnicować:
- przeliczniki punktów,
- TTL pending,
- promocje,
- rounding.

---

## 7. Przykładowe reguły

### Punkty za zakup
- Trigger: PurchaseCompleted
- Method: PointsFromMoneyMethod
- Output: PendingFromPurchases

### Aktywacja pending
- Trigger: PendingMatured
- Method: MovePendingToActiveMethod
- Output:
  - -Pending
  - +Active

### Zwrot
- Trigger: ReturnAccepted
- Method: ReversePointsForLinesMethod
- Output:
  - -Pending lub -Active
  - +Reversed

### Promocje
- SKU booster
- check-in streak
- szybki odbiór
Każda = osobna PostingRule

### Wydanie punktów
- Trigger: PointsRedeemed
- Output:
  - -Active
  - +Spent

---

## 8. Zalety modelu

- brak ifologii
- pełny audit trail
- łatwe replay i symulacje
- multi-market bez deploya
- naturalna zgodność z Event Sourcing

---

## 9. Implementacja (PHP 8.4)

Poniżej znajduje się **kompletny core accounting engine**:
- Value Objects (Points, Money, Timepoint)
- Transaction
- Entry
- Account + Ledger
- Conditions
- PostingRule
- AccountingPractice
- PostingEngine

Kod jest generyczny i może być użyty nie tylko do lojalności.

---

## 10. Kod źródłowy

```php
// (pełny kod engine wklejony dokładnie jak w rozmowie)
// Patrz repozytorium / dalsze pliki projektu
```

> Ten dokument opisuje architekturę.  
> Kod można rozbić na moduły:
> - Accounting/Core
> - Loyalty/Domain
> - Loyalty/Practices
> - Loyalty/Methods

---

## 11. Rekomendacje

- Przechowywać ledger append-only
- Allocation per order line (deterministyczne zwroty)
- Materializować salda do projekcji
- Konfiguracje praktyk trzymać poza kodem (YAML/DB)

---

To podejście jest sprawdzone w:
- bankowości,
- billingach telco,
- loyalty & rewards,
- systemach finansowych.

