<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use SoftwareArchetypes\Accounting\Loyalty\Domain\AccountingPractice;
use SoftwareArchetypes\Accounting\Loyalty\Domain\AccountType;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccount;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccountId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\MarketId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Points;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRules\MaturationPeriodExpiredPostingRule;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRules\PointsRedeemedPostingRule;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRules\PromotionAwardedPostingRule;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRules\PurchaseCompletedPostingRule;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRules\ReturnAcceptedPostingRule;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions\MaturationPeriodExpired;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions\PointsRedeemed;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions\PromotionAwarded;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions\PurchaseCompleted;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions\ReturnAccepted;
use SoftwareArchetypes\Accounting\Money;

echo "=== E-Commerce Loyalty Program - Entry-based Ledger Demo ===\n\n";

// 1. Setup AccountingPractice for Polish market
echo "1. Setting up AccountingPractice for Poland\n";
$practicePL = AccountingPractice::forMarket(
    MarketId::fromString('PL'),
    'Poland',
    pointsPerCurrencyUnit: 10,       // 10 points per 1 PLN
    maturationPeriodDays: 14,         // 14 days return period
    pointsExpirationDays: 365,        // Points expire after 1 year
    roundDown: true,
    promotionalMultipliers: [
        'JACKET-001' => 2.0,          // 2x points for jackets
    ]
);
echo "   ✓ Poland: 10 points/PLN, 14 days maturation\n\n";

// 2. Create customer loyalty account
echo "2. Creating loyalty account for Jan Kowalski\n";
$accountId = LoyaltyAccountId::generate();
$account = LoyaltyAccount::create(
    $accountId,
    'CUST-001',
    'Jan Kowalski',
    $practicePL
);
echo "   ✓ Account created: {$accountId->toString()}\n\n";

// 3. Register posting rules
echo "3. Registering posting rules\n";
$account->registerPostingRule(new PurchaseCompletedPostingRule());
$account->registerPostingRule(new ReturnAcceptedPostingRule());
$account->registerPostingRule(new PromotionAwardedPostingRule());
$account->registerPostingRule(new MaturationPeriodExpiredPostingRule());
$account->registerPostingRule(new PointsRedeemedPostingRule());
echo "   ✓ 5 posting rules registered\n\n";

// 4. Process a purchase
echo "4. Processing purchase: 100 PLN (2 items)\n";
$purchaseId = 'PURCHASE-001';
$purchase = new PurchaseCompleted(
    'TXN-001',
    $purchaseId,
    'CUST-001',
    Money::of(10000), // 100 PLN
    [
        'item1' => [
            'lineItemId' => 'LINE-001',
            'amount' => Money::of(5000), // 50 PLN
            'productId' => 'SHIRT-001',
        ],
        'item2' => [
            'lineItemId' => 'LINE-002',
            'amount' => Money::of(5000), // 50 PLN
            'productId' => 'JACKET-001', // Has 2x multiplier!
        ],
    ],
    MarketId::fromString('PL'),
    new DateTimeImmutable('2024-01-01')
);

$account->processTransaction($purchase);

echo "   Active Points: {$account->activePoints()->amount()}\n";
echo "   Pending Points: {$account->totalPendingPoints()->amount()}\n";
echo "   Details:\n";
echo "     - LINE-001 (shirt): 500 points pending\n";
echo "     - LINE-002 (jacket with 2x bonus): 1000 points pending\n";
echo "     - Total: 1500 points pending until 2024-01-15\n\n";

// 5. Award promotional points
echo "5. Awarding promotional bonus (check-in streak)\n";
$promo = new PromotionAwarded(
    'TXN-002',
    'CUST-001',
    'PROMO-CHECKIN-7DAYS',
    'check-in-streak',
    Points::of(100),
    true, // immediateActivation
    null, // referenceId
    new DateTimeImmutable('2024-01-02')
);

$account->processTransaction($promo);

echo "   Active Points: {$account->activePoints()->amount()} (promotional points are immediate)\n";
echo "   Pending Points: {$account->totalPendingPoints()->amount()}\n\n";

// 6. Return one item before maturation
echo "6. Customer returns jacket (LINE-002) before maturation\n";
$return = new ReturnAccepted(
    'TXN-003',
    $purchaseId,
    'CUST-001',
    ['LINE-002'], // Return jacket
    new DateTimeImmutable('2024-01-05')
);

$account->processTransaction($return);

echo "   Active Points: {$account->activePoints()->amount()}\n";
echo "   Pending Points: {$account->totalPendingPoints()->amount()} (1000 points reversed from pending)\n";
echo "   Reversed Points: {$account->reversedPoints()->amount()}\n\n";

// 7. Maturation period expires - activate remaining pending points
echo "7. Maturation period expired - activating pending points\n";

// Find pending entries to activate
$pendingAccount = $account->account(AccountType::PENDING_FROM_PURCHASES);
$pendingEntryIds = array_map(
    fn($entry) => $entry->id()->toString(),
    $pendingAccount->entries()
);

if (!empty($pendingEntryIds)) {
    $maturation = new MaturationPeriodExpired(
        'TXN-004',
        'CUST-001',
        AccountType::PENDING_FROM_PURCHASES,
        $pendingEntryIds,
        new DateTimeImmutable('2024-01-16')
    );

    $account->processTransaction($maturation);
}

echo "   Active Points: {$account->activePoints()->amount()} (500 from purchase + 100 from promo)\n";
echo "   Pending Points: {$account->totalPendingPoints()->amount()}\n\n";

// 8. Redeem points
echo "8. Customer redeems 200 points for a voucher\n";
$redemption = new PointsRedeemed(
    'TXN-005',
    'CUST-001',
    Points::of(200),
    'REDEMPTION-001',
    'voucher',
    new DateTimeImmutable('2024-01-20')
);

$account->processTransaction($redemption);

echo "   Active Points: {$account->activePoints()->amount()}\n";
echo "   Spent Points: {$account->spentPoints()->amount()}\n\n";

// 9. Display complete ledger
echo "9. Complete Ledger (all entries)\n";
echo "   " . str_repeat('=', 80) . "\n";

foreach (AccountType::cases() as $accountType) {
    $balance = $account->balance($accountType);
    $entries = $account->account($accountType)->entries();

    if ($balance->isZero() && empty($entries)) {
        continue; // Skip empty accounts
    }

    echo "   {$accountType->displayName()}: {$balance->amount()} points\n";

    foreach ($entries as $entry) {
        $sign = $entry->amount()->amount() >= 0 ? '+' : '';
        echo "     {$sign}{$entry->amount()->amount()} | {$entry->description()}\n";
    }
    echo "\n";
}

echo "=== Summary ===\n";
echo "Total Active: {$account->activePoints()->amount()}\n";
echo "Total Spent: {$account->spentPoints()->amount()}\n";
echo "Total Reversed: {$account->reversedPoints()->amount()}\n";
echo "Total Pending: {$account->totalPendingPoints()->amount()}\n";
echo "\nTotal Transactions Processed: " . count($account->transactions()) . "\n";
echo "Total Ledger Entries: " . count($account->allEntries()) . "\n";

echo "\n✓ Demo completed successfully!\n";
