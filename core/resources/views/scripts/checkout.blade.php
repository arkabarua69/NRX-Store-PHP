<script>
    $(document).ready(function() {
        const $playerId = $('#player_id');
        const $playerIdError = $("#player_id_error");
        const $gameAccount = $('#game_account');
        const $gameAccountError = $("#game_account_error");
        const $gamePassword = $('#game_password');
        const $gamePasswordError = $("#game_password_error");
        const $addMoneyInstruction = $('#add_money_instruction');
        const $wallet = $('#wallet');
        const $walletBalance = $('#wallet_balance');
        const $variationId = $('#variation_id');
        const $variationPrice = $('#variation_price');
        const $totalCost = $('#total_cost');
        const $paymentMethod = $('#payment_method');
        const $quantityInput = $('#quantity');
        const $buyNow = $("#buy_now");
        const $addFund = $("#add_fund");

        function showError($element, message) {
            $element.html(`<div class='alert alert-white alert-p5 m-lr-7'>${message}</div>`);
        }

        function clearError($element) {
            $element.html("");
        }

        function handleInputError($input, $errorElement, message) {
            $input.on('keyup', function() {
                if ($(this).val() === "") {
                    showError($errorElement, message);
                } else {
                    clearError($errorElement);
                }
            });
        }

        handleInputError($playerId, $playerIdError, "Player id required");
        handleInputError($gameAccount, $gameAccountError, "Gmail/number required");
        handleInputError($gamePassword, $gamePasswordError, "Password required");

        $('#payment_gateway').on('click', function() {
            $addMoneyInstruction.show();
        });

        $wallet.on('click', function() {
            $addMoneyInstruction.hide();
        });

        function selectVariation() {

            $('.variation_list').click(function() {
                var clickedVariation = $(this);
                var hasStockoutClass = clickedVariation.hasClass('stockout');

                if (!hasStockoutClass) {
                    $('.variation_list').removeClass('selected_variation');
                    clickedVariation.addClass('selected_variation');
                    $('.variation_list').each(function() {
                        var svg = $(this).find('svg');
                        if ($(this).hasClass('selected_variation')) {
                            svg.attr('data-icon', 'check-circle');
                            svg.html('<path fill="currentColor" d="M504 256c0 136.967-111.033 248-248 248S8 392.967 8 256 119.033 8 256 8s248 111.033 248 248zM227.314 387.314l184-184c6.248-6.248 6.248-16.379 0-22.627l-22.627-22.627c-6.248-6.249-16.379-6.249-22.628 0L216 308.118l-70.059-70.059c-6.248-6.248-16.379-6.248-22.628 0l-22.627 22.627c-6.248 6.248-6.248 16.379 0 22.627l104 104c6.249 6.249 16.379 6.249 22.628.001z"></path>');
                            svg.css('color', 'var(--theme-color)');
                        } else {
                            svg.attr('data-icon', 'circle');
                            svg.html('<path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path>');
                            svg.css('color', '');
                        }
                    });
                    $('#quantity').val("1");
                    $('#variation_id').val(clickedVariation.attr('id'));
                    $('#variation_price').val(clickedVariation.data('price'));
                    enableBuyNow();
                    const unitCost = parseFloat($variationPrice.val());
                    autoSelectPaymentMethod(unitCost);
                    if (unitCost !== "" && unitCost !== "undifnied") {
                        $totalCost.text(unitCost.toFixed(0));
                    } else {
                        $totalCost.text("0");
                    }
                    checkWallet();

                }
            });


            /*
            $('.variation_list').on('click', function() {
                const $clickedVariation = $(this);
                if (!$clickedVariation.hasClass('stockout')) {
                    $('.variation_list').removeClass('selected_variation');
                    $clickedVariation.addClass('selected_variation');
                    $('.selected_variation_icon').remove();
                    $clickedVariation.prepend(
                        '<i class="selected_variation_icon fa-solid fa-check"></i>');
                    $variationId.val($clickedVariation.attr('id'));
                    $variationPrice.val($clickedVariation.data('price'));
                    $totalCost.text($clickedVariation.data('price'));
                    checkWallet();
                }
            });
            */
        }

        function selectPaymentMethod() {
            $('.pm_list').click(function() {
                var clickedPM = $(this);
                $('.pm_list .check_selected').removeClass('element-check-label');
                clickedPM.find('.check_selected').addClass('element-check-label');
                $('#payment_method').val(clickedPM.attr('id'));
                checkWallet();
            });
            /*
            $('.pm_list').on('click', function() {
                const $clickedPM = $(this);
                $('.pm_list').removeClass('selected_pm');
                $clickedPM.addClass('selected_pm');
                $('.selected_pm_icon').remove();
                $clickedPM.prepend('<i class="selected_pm_icon fa-solid fa-check"></i>');
                $paymentMethod.val($clickedPM.attr('id'));
                checkWallet();
            });
            */
        }

        function checkWallet() {
            const variationPrice = parseFloat($variationPrice.val());
            const paymentMethod = $paymentMethod.val();
            const walletBalance = parseFloat($walletBalance.text());


            if ($('#quantity').length) {
                var getQuantity = $('#quantity').val();
            } else {
                var getQuantity = 1;
            }
            var calNewCost = variationPrice * getQuantity;
            // console.log(calNewCost);

            if (!isNaN(calNewCost)) {
                if (paymentMethod === "wallet" && calNewCost > walletBalance) {
                    disableBuyNow();
                } else {
                    enableBuyNow();
                }
            }


        }

        function disableBuyNow() {
            $buyNow.prop("disabled", true);
            $addFund.show();
        }

        function enableBuyNow() {
            $buyNow.prop("disabled", false);
            $addFund.hide();
        }

        function handleQuantityChange() {
            $(document).on('click', '.quantity-options div', function() {
                var $quantityInput = $('#quantity');
                var currentValue = parseInt($quantityInput.val());
                //console.log($('#quantity').val());
                //console.log(currentValue);
                if ($(this).is('#decrease') && currentValue > 1) {
                    $quantityInput.val(currentValue - 1);
                    //console.log('d'+currentValue);
                } else if ($(this).is('#increase')) {
                    $quantityInput.val(currentValue + 1);
                    //console.log('i'+currentValue);
                }
                const unitCost = parseFloat($variationPrice.val());
                const newQuantity = parseInt($quantityInput.val());
                const newCost = unitCost * newQuantity;

                if (newCost !== "" && newCost !== "undifnied") {
                    $totalCost.text(newCost.toFixed(0));
                } else {
                    $totalCost.text("0");
                }
                autoSelectPaymentMethod(newCost);
            });

            $(document).on('change', '#quantity', function() {
                const unitCost = parseFloat($variationPrice.val());
                const newQuantity = parseInt($quantityInput.val());
                const newCost = unitCost * newQuantity;

                if (newCost !== "" && newCost !== "undifnied") {
                    $totalCost.text(newCost.toFixed(0));
                } else {
                    $totalCost.text("0");
                }
                autoSelectPaymentMethod(newCost);
            });

            /*
            $(document).on('click', '.quantity-options i', function() {
                let currentValue = parseInt($quantityInput.val());

                if ($(this).is('#decrease') && currentValue > 1) {
                    $quantityInput.val(currentValue - 1);
                } else if ($(this).is('#increase')) {
                    $quantityInput.val(currentValue + 1);
                }

                const unitCost = parseFloat($variationPrice.val());
                const newQuantity = parseInt($quantityInput.val());
                const newCost = unitCost * newQuantity;

                $totalCost.text(newCost.toFixed(2));
            });

            */
        }

        function autoSelectPaymentMethod(cost) {
            if ($walletBalance.text() < cost) {
                $('#payment_gateway').click();
            }

            if ($('#payment_method').val() == "wallet" && cost > $walletBalance.text()) {
                disableBuyNow();
            } else {
                enableBuyNow();
            }

        }

        function initializePaymentMethod() {
            if ($walletBalance.text() !== "") {
                if (parseFloat($walletBalance.text()) > 0) {
                    $wallet.click();
                } else {
                    $('#payment_gateway').click();
                }
            } else {
                $wallet.click();
            }
        }

        // Initialize event handlers
        selectVariation();
        selectPaymentMethod();
        handleQuantityChange();
        initializePaymentMethod();
    }); </script>