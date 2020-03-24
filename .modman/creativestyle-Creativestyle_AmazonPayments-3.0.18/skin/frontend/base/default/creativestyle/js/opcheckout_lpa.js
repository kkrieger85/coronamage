var AmazonPaymentsOPC = (function() {

    var refs = {
        checkout: null,
        billing: null,
        shipping: null,
        shippingMethod: null,
        payment: null
    };

    var flags = {
        shippingAddressSelected: false,
        shippingAddressLoading: false,
        paymentSelected: false,
        paymentLoading: false,
        submitAllowed: false
    };

    var options = {
        live: true,
        formKey: null,
        continueButtonLabel: 'Continue',
        pleaseWait: 'Please wait...'
    };

    var checkoutChangeSectionWrapper = function (fn) {
        return function (section) {
            var step = section.replace('opc-', '');
            switch (step) {
                case 'billing':
                case 'shipping':
                case 'payment':
                    section = 'opc-lpa';
                    break;
                default:
                    break;
            }
            return fn.apply(this, [section]);
        }
    };

    var checkoutGotoSectionWrapper = function (fn) {
        return function (section, reloadProgressBlock) {
            switch (section) {
                case 'billing':
                case 'shipping':
                    arguments[0] = 'lpa';
                    break;
                case 'payment':
                    arguments[0] = 'review';
                    break;
                default:
                    break;
            }
            return fn.apply(this, arguments);
        }
    };

    var paymentOnSaveWrapper = function (fn) {
        return function () {
            refs.checkout.reloadProgressBlock('payment');
            return fn.apply(this, arguments);
        }
    };

    var paymentOnCompleteWrapper = function (fn) {
        return function () {
            refs.checkout.loadWaiting = 'shipping-method';
            refs.shippingMethod.resetLoadWaiting();
            return fn.apply(this, arguments);
        }
    };

    var paymentValidatePrototype = function () {
        return true;
    };

    var shippingMethodOnSavePrototype = function (transport) {
        var response = transport.responseJSON || transport.responseText.evalJSON(true) || {};

        if (response.error) {
            alert(response.message.stripTags().toString());
            return false;
        }

        if (response.update_section) {
            $('checkout-'+response.update_section.name+'-load').update(response.update_section.html);
        }

        if (response.goto_section) {
            checkout.reloadProgressBlock();
            return;
        }
    };

    var shippingMethodOnCompleteWrapper = function (fn) {
        return function () {
            refs.checkout.loadWaiting = false;
            refs.payment.save();
            return fn.apply(this, arguments);
        }
    };

    var reviewSavePrototype = function () {
        refs.checkout.setLoadWaiting('review');
        AmazonPayments.saveOrder();
    };

    var nextStepButtonClick = function () {
        refs.checkout.reloadProgressBlock('billing');
        refs.checkout.reloadProgressBlock('shipping');
        refs.checkout.gotoSection('shipping_method')
    };

    var createLpaContainer = function (addressBookId, walletId) {
        var opcStepContainer = document.createElement('li');
        opcStepContainer.id = 'opc-lpa';
        opcStepContainer.className = 'section';

        var lpaStepContainer = document.createElement('div');
        lpaStepContainer.className = 'step a-item';

        var addressBookWidgetContainer = document.createElement('div');
        addressBookWidgetContainer.id = addressBookId;
        lpaStepContainer.append(addressBookWidgetContainer);

        var walletWidgetContainer = document.createElement('div');
        walletWidgetContainer.id = walletId;
        lpaStepContainer.append(walletWidgetContainer);

        var continueButtonContainer = document.createElement('div');
        continueButtonContainer.id = 'lpa-buttons-container';
        continueButtonContainer.className = 'buttons-set';

        var continueButton = document.createElement('button');
        continueButton.id = 'amazonpayments-opc-next-step';
        continueButton.type = 'button';
        continueButton.className = 'button';
        continueButton.title = options.continueButtonLabel;
        Event.observe(continueButton, 'click', nextStepButtonClick);
        continueButton.innerHTML = '<span><span>' + options.continueButtonLabel + '</span></span>';
        continueButtonContainer.append(continueButton);

        var pleaseWait = document.createElement('span');
        pleaseWait.id = 'lpa-please-wait';
        pleaseWait.className = 'please-wait';
        pleaseWait.setStyle({display: 'none'});
        pleaseWait.innerHTML = options.pleaseWait;

        continueButtonContainer.append(pleaseWait);

        lpaStepContainer.append(continueButtonContainer);
        opcStepContainer.append(lpaStepContainer);

        refs.checkout.accordion.container.prepend(opcStepContainer);
        refs.checkout.accordion.sections[0].removeClassName('allow');
        refs.checkout.gotoSection('lpa');
        updateControls();
    };

    var updateControls = function () {
        var buttonsContainer = $('lpa-buttons-container');
        if (buttonsContainer) {
            if (flags.shippingAddressSelected && flags.paymentSelected &&
                !flags.shippingAddressLoading && !flags.paymentLoading) {
                buttonsContainer.removeClassName('disabled');
                refs.checkout._disableEnableAll(buttonsContainer, false);
            } else {
                buttonsContainer.addClassName('disabled');
                refs.checkout._disableEnableAll(buttonsContainer, true);
            }
        }

        var pleaseWait = $('lpa-please-wait');
        if (pleaseWait) {
            if (flags.shippingAddressLoading || flags.paymentLoading) {
                pleaseWait.show();
            } else {
                pleaseWait.hide();
            }
        }

        var submitContainer = $('review-buttons-container');
        if (submitContainer) {
            if (flags.shippingAddressSelected && flags.paymentSelected && flags.submitAllowed) {
                submitContainer.removeClassName('disabled');
                submitContainer.setStyle({opacity: 1});
                refs.checkout._disableEnableAll(submitContainer, false);
            } else {
                submitContainer.addClassName('disabled');
                submitContainer.setStyle({opacity: 0.5});
                refs.checkout._disableEnableAll(submitContainer, true);
            }
        }
    };

    var onShippingAddressSelect = function (selected, loading) {
        flags.shippingAddressSelected = selected;
        flags.shippingAddressLoading = loading;
        updateControls();
    };

    var onPaymentSelect = function (selected, loading) {
        flags.paymentSelected = selected;
        flags.paymentLoading = loading;
        updateControls();
    };

    var onCheckoutSubmitChange = function (allowed) {
        flags.submitAllowed = allowed;
        updateControls();
    };

    var onXhrResponse = function (response) {
        if (response.update_sections) {
            response.update_sections.each(function(section) {
                var sectionEl = $('checkout-' + section.name + '-load');
                sectionEl && sectionEl.update(section.html);
            });
        }

        if (response.allow_sections) {
            response.allow_sections.each(function(section) {
                var sectionEl = $('opc-' + section);
                sectionEl && sectionEl.addClassName('allow');
            });
        }

        if (response.render_widget && response.disable_widget) {
            if (response.render_widget['wallet'] && response.disable_widget['address-book']) {
                refs.checkout.gotoSection('lpa');
                $('amazonpayments-opc-next-step').onclick = function () {
                    refs.checkout.reloadProgressBlock('shipping_method');
                    refs.checkout.reloadProgressBlock('payment');
                    refs.checkout.gotoSection('review');
                }
            }
        }
    };

    var setupRefs = function () {
        refs.checkout && (refs.checkout.changeSection = checkoutChangeSectionWrapper(refs.checkout.changeSection).bind(refs.checkout));
        refs.checkout && (refs.checkout.gotoSection = checkoutGotoSectionWrapper(refs.checkout.gotoSection).bind(refs.checkout));

        refs.shippingMethod && (refs.shippingMethod.onSave = shippingMethodOnSavePrototype.bind(refs.shippingMethod));
        refs.shippingMethod && (refs.shippingMethod.onComplete = shippingMethodOnCompleteWrapper(refs.shippingMethod.onComplete).bind(refs.shippingMethod));

        refs.payment && (refs.payment.form = document.createElement('form'));
        refs.payment && (refs.payment.form.innerHTML = '<input type="text" name="payment[method]" value="amazonpayments_advanced' + (options.live ? '' : '_sandbox') + '"/>' + (options.formKey ? '<input type="hidden" name="form_key" value="' + options.formKey + '">' : ''));

        refs.payment && (refs.payment.onSave = paymentOnSaveWrapper(refs.payment.onSave).bind(refs.payment));
        refs.payment && (refs.payment.onComplete = paymentOnCompleteWrapper(refs.payment.onComplete).bind(refs.payment));
        refs.payment && (refs.payment.validate = paymentValidatePrototype.bind(refs.payment));

        Review.prototype.save = reviewSavePrototype;
    };

    var initOPC = function (checkout, billing, shipping, shippingMethod, payment, customOptions) {
        refs.checkout = checkout;
        refs.billing = billing;
        refs.shipping = shipping;
        refs.shippingMethod = shippingMethod;
        refs.payment = payment;
        for (var propertyName in customOptions) {
            options[propertyName] = customOptions[propertyName];
        }
        setupRefs();
    };

    return {
        createContainer: createLpaContainer,
        onShippingAddressSelect: onShippingAddressSelect,
        onPaymentSelect: onPaymentSelect,
        onCheckoutSubmitChange: onCheckoutSubmitChange,
        onXhrResponse: onXhrResponse,
        init: initOPC
    }

})();
