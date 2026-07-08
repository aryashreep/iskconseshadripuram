<?php

namespace Isjm\Donations;

/**
 * HTML rendering functions for the donation system.
 * 
 * Handles: CTA buttons, seva option forms, donation section layouts.
 */
class DonationRenderer
{
    private DonationService $service;

    public function __construct(?DonationService $service = null)
    {
        $this->service = $service ?? new DonationService();
    }

    /**
     * Render a donation CTA button for a specific cause.
     * 
     * Usage:
     *   $renderer->renderCTA(['cause_slug' => 'janmashtami', 'label' => 'Offer Seva']);
     */
    public function renderCTA(array $options = []): void
    {
        $slug = $options['cause_slug'] ?? '';
        $label = $options['label'] ?? 'Donate Now';
        $mode = $options['mode'] ?? 'one_time';
        $btnStyle = $options['button_style'] ?? 'primary';
        $size = $options['size'] ?? 'sm';
        $icon = $options['icon'] ?? 'fa-heart';

        $url = BASE_URL . 'donate/' . urlencode($slug);
        if ($mode === 'monthly') {
            $url .= '&mode=monthly';
        }

        $baseClass = "btn btn-{$btnStyle} btn-{$size}";
        ?>
        <a href="<?= htmlspecialchars($url) ?>" 
           class="<?= $baseClass ?>" 
           style="display:inline-flex; align-items:center; gap:6px; text-decoration:none;"
           data-cause="<?= htmlspecialchars($slug) ?>"
           data-mode="<?= htmlspecialchars($mode) ?>">
            <i class="fas <?= htmlspecialchars($icon) ?>"></i>
            <?= htmlspecialchars($label) ?>
        </a>
        <?php
    }

    /**
     * Render a full donation section with grouped sevas for a cause.
     * 
     * @param array $cause
     * @param array $groupedSevas
     * @param string|null $formTypeOverride
     */
    public function renderSevaOptions(array $cause, array $groupedSevas, ?string $formTypeOverride = null): void
    {
        $preview = isset($cause['is_preview']) && $cause['is_preview'];
        $formType = $formTypeOverride ?? $cause['form_type'] ?? 'tiers';
        ?>
        <div class="donation-options">
            <?php if ($formType === 'quantity'): ?>
                <?= $this->renderQuantityForm($groupedSevas) ?>
                
            <?php elseif ($formType === 'multi_item'): ?>
                <?= $this->renderMultiItemForm($groupedSevas) ?>
                
            <?php elseif ($formType === 'cart' || $formType === 'cart_qty'): ?>
                <?= $this->renderCartQtyForm($groupedSevas) ?>
                
            <?php else: ?>
                <?= $this->renderTieredForm($cause, $groupedSevas) ?>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render quantity-based form (e.g., Shastra Daan).
     */
    private function renderQuantityForm(array $groupedSevas): void
    {
        ?>
        <div class="quantity-form">
            <?php foreach ($groupedSevas as $group): ?>
                <?php foreach ($group['items'] as $item): ?>
                    <div class="quantity-item" data-seva-id="<?= $item['id'] ?>" data-per-unit="<?= $item['amount'] ?>">
                        <div class="quantity-item-info">
                            <span class="quantity-item-name"><?= htmlspecialchars($item['name']) ?></span>
                            <span class="quantity-item-price"><?= $this->service->formatAmount((float)$item['amount']) ?>/unit</span>
                        </div>
                        <div class="quantity-item-input">
                            <label>Qty:</label>
                            <input type="number" class="qty-input" min="0" max="1000" value="0" data-price="<?= $item['amount'] ?>">
                            <span class="qty-total">= <?= $this->service->formatAmount(0) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <div class="quantity-grand-total">
                <span>Total:</span>
                <span class="grand-total-amount"><?= $this->service->formatAmount(0) ?></span>
            </div>
        </div>
        <?php
    }

    /**
     * Render multi-item cart form (e.g., Tula Daan).
     */
    private function renderMultiItemForm(array $groupedSevas): void
    {
        ?>
        <div class="multi-item-form">
            <?php foreach ($groupedSevas as $group): ?>
                <?php foreach ($group['items'] as $item): ?>
                    <div class="cart-item" data-seva-id="<?= $item['id'] ?>" data-rate="<?= $item['amount'] ?>">
                        <div class="cart-item-header">
                            <span class="cart-item-name"><?= htmlspecialchars($item['name']) ?></span>
                            <span class="cart-item-rate"><?= $this->service->formatAmount((float)$item['amount']) ?>/kg</span>
                        </div>
                        <div class="cart-item-input">
                            <label>Weight (kg):</label>
                            <input type="number" class="cart-qty" min="0" max="10000" step="0.5" value="0" 
                                   data-rate="<?= $item['amount'] ?>" 
                                   data-seva-id="<?= $item['id'] ?>" 
                                   data-name="<?= htmlspecialchars($item['name']) ?>">
                            <span class="cart-item-total"><?= $this->service->formatAmount(0) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <div class="cart-grand-total">
                <span>Grand Total:</span>
                <span class="cart-grand-total-amount"><?= $this->service->formatAmount(0) ?></span>
            </div>
        </div>
        <?php
    }

    /**
     * Render cart with +/- quantity buttons.
     */
    private function renderCartQtyForm(array $groupedSevas): void
    {
        ?>
        <div class="donation-cart-qty-form">
            <?php foreach ($groupedSevas as $catSlug => $group): 
                $cat = $group['category'];
            ?>
                <?php if ($cat['name']): ?>
                <div class="seva-category-label">
                    <i class="fas <?= htmlspecialchars($cat['icon']) ?>"></i>
                    <?= htmlspecialchars($cat['name']) ?>
                </div>
                <?php endif; ?>
                
                <?php foreach ($group['items'] as $item): 
                    $maxQty = ($item['max_quantity'] ?? 0) > 0 ? $item['max_quantity'] : 99;
                ?>
                <div class="cart-qty-item" data-seva-id="<?= $item['id'] ?>" data-price="<?= (float)$item['amount'] ?>" data-name="<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>" data-max="<?= $maxQty ?>">
                    <div class="cart-qty-info">
                        <div class="cart-qty-name"><?= htmlspecialchars($item['name']) ?></div>
                        <?php if ($item['description']): ?>
                        <div class="cart-qty-desc"><?= htmlspecialchars($item['description']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="cart-qty-controls">
                        <div class="cart-qty-price">₹<?= number_format((float)$item['amount']) ?></div>
                        <div class="qty-selector">
                            <button type="button" class="qty-btn qty-minus">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span class="qty-count" id="qty-count-<?= $item['id'] ?>">0</span>
                            <button type="button" class="qty-btn qty-plus">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div class="cart-qty-line-total" id="line-total-<?= $item['id'] ?>">₹0</div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
            
            <!-- Cart Summary -->
            <div class="cart-qty-summary" id="cartQtySummary" style="display:none;">
                <div class="cart-qty-summary-header">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Selected Sevas</span>
                    <span class="cart-qty-total-items" id="cartTotalItems">0 items</span>
                </div>
                <div class="cart-qty-summary-items" id="cartSummaryItems">
                    <!-- Dynamically populated -->
                </div>
                <div class="cart-qty-grand-total">
                    <span>Total Donation</span>
                    <span class="cart-grand-amount" id="cartGrandAmount">₹0</span>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render tiered radio options (default form type).
     */
    private function renderTieredForm(array $cause, array $groupedSevas): void
    {
        ?>
        <div class="amount-options" id="amountOptions">
            <?php 
            $first = true;
            foreach ($groupedSevas as $catSlug => $group): 
                $cat = $group['category'];
            ?>
                <?php if ($cat['name']): ?>
                <div class="seva-category-label">
                    <i class="fas <?= htmlspecialchars($cat['icon']) ?>"></i>
                    <?= htmlspecialchars($cat['name']) ?>
                </div>
                <?php endif; ?>
                
                <?php foreach ($group['items'] as $item): 
                    $active = $first ? 'active' : '';
                    $first = false;
                ?>
                <div class="amount-option <?= $active ?>"
                     data-amount="<?= (int)$item['amount'] ?>"
                     data-seva-id="<?= $item['id'] ?>"
                     onclick="selectDonationOption(this)">
                    <div class="amount-option-radio"></div>
                    <div class="amount-option-content">
                        <div class="amount-option-name"><?= htmlspecialchars($item['name']) ?></div>
                        <?php if ($item['description']): ?>
                        <div class="amount-option-desc"><?= htmlspecialchars($item['description']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="amount-option-price"><?= $this->service->formatAmount((float)$item['amount']) ?></div>
                </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
            
            <!-- Custom Amount -->
            <div class="custom-amount-row" onclick="toggleCustomAmount()">
                <div class="plus-icon"><i class="fas fa-plus"></i></div>
                <span>Custom Amount</span>
            </div>
            <div class="custom-amount-input-wrap" id="customAmountWrap">
                <label for="customAmount">Enter your amount</label>
                <div class="input-group">
                    <span class="input-currency">₹</span>
                    <input type="number" id="customAmount" min="<?= (int)$cause['min_amount'] ?>" 
                           max="1000000" placeholder="Enter amount" step="1">
                </div>
            </div>
        </div>
        <?php
    }
}
