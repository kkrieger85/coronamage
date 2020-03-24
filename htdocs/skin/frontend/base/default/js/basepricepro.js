
/**
 * Der Modulprogrammierer - Vinai Kopp, Rico Neitzel GbR
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the
 * Der Modulprogrammierer - COMMERCIAL SOFTWARE LICENSE (v1.0) (DMCSL 1.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.der-modulprogrammierer.de/licenses/dmcsl-1.0.html
 *
 *
 * @category   DerModPro
 * @package    DerModPro_BasePricePro
 * @copyright  Copyright (c) 2009 Der Modulprogrammierer - Vinai Kopp, Rico Neitzel GbR
 * @copyright  Copyright (c) 2012 Netresearch GmbH 
 * @license    http://www.der-modulprogrammierer.de/licenses/dmcsl-1.0.html (DMCSL 1.0)
 */

if (typeof BasePrice=='undefined') {
	var BasePrice = {};
}

/*
 * BasePricePro JS Class
 */
BasePrice.Converter = Class.create({
	initialize: function(config){
		this.config = config;
		this.currentProductId = 0;
	},
	setCurrentProductId: function(productId){
		this.currentProductId = productId;
	},
	getCurrentProductId: function(){
		if (typeof window.BCP == 'undefined') {
			return this.currentProductId;
		} else {
			var obj = this.getProductObject();
			return obj.getBcpProcessingProductId();
		}
	},
	getBasePriceLabel: function(productPriceInclTax, productPriceExclTax){
		if (! this.config.productAmount || ! parseFloat(this.config.rate) || ! parseFloat(this.config.referenceAmount)) return '';
	
		var id = this.getCurrentProductId();
		var productAmount = parseFloat(id && this.config.optionAmounts[id] ? this.config.optionAmounts[id] : this.config.productAmount);;
		var productPrice = window.basePrice.inclTax ? productPriceInclTax : productPriceExclTax;
		if (! productPrice) return '';
		var label = window.basePrice.format;
		var basePrice = productPrice / productAmount / parseFloat(this.config.rate) * parseFloat(this.config.referenceAmount);
		label = label.replace(/{{reference_unit}}/, this.config.referenceUnit);
		label = label.replace(/{{reference_unit_short}}/, this.config.referenceUnitShort);
		label = label.replace(/{{reference_amount}}/, this.config.referenceAmount);
		var formater = this.getProductObject();
		label = label.replace(/{{baseprice}}/, formater.formatPrice(basePrice, false));
		return label;
	},
	getProductObject: function() {
		return typeof window.spConfig=='undefined' ? window.optionsPrice : window.spConfig;
	}
});

if (typeof Product != 'undefined') {
	
	/*
	 * Extending Product.Config is only needed if the better configurable product module is not loaded
	 */
	if (typeof BCP == 'undefined') {
		BasePrice.Config = Class.create(Product.Config, {
			
			configureElement: function($super, element) {
				// find the selected product id (if one)
				if (typeof window.basePrice.product != 'undefined') {
					var ids = new Array();
					var tmp = new Array();
					this.settings.each(function(element){
						if (!element.attributeId || !element.selectedIndex) 
							return;
						if (!ids.length) 
							ids = element.options[element.selectedIndex].config.products;
						else {
							tmp = new Array();
							for (var i = 0; i < ids.length; i++) {
								if (element.options[element.selectedIndex].config.products.indexOf(ids[i]) > -1) 
									tmp.push(ids[i]);
							}
							ids = tmp;
						}
					}.bind(this));
					var id = ids.length ? ids[0] : 0;
					window.basePrice.product.setCurrentProductId(id);
				}
				$super(element);
			}
		});
		Product.Config = BasePrice.Config;
	}

	/*
	 * This is compatible with the Better Configurable Product JS
	 */
	BasePrice.OptionsPrice = Class.create(Product.OptionsPrice, {
	
	    reload: function() {
	        var price;
	        var formattedPrice;
	        var optionPrices = this.getOptionPrices();
	        var nonTaxable = optionPrices[1];
	        optionPrices = optionPrices[0];
	        $H(this.containers).each(function(pair) {
	            var _productPrice;
	            var _plusDisposition;
	            var _minusDisposition;
	            if ($(pair.value)) {
	                if (pair.value == 'old-price-'+this.productId && this.productOldPrice != this.productPrice) {
	                    _productPrice = this.productOldPrice;
	                    _plusDisposition = this.oldPlusDisposition;
	                    _minusDisposition = this.oldMinusDisposition;
	                } else {
	                    _productPrice = this.productPrice;
	                    _plusDisposition = this.plusDisposition;
	                    _minusDisposition = this.minusDisposition;
	                }
	
	                var price = optionPrices+parseFloat(_productPrice)
	                if (this.includeTax == 'true') {
	                    // tax = tax included into product price by admin
	                    var tax = price / (100 + this.defaultTax) * this.defaultTax;
	                    var excl = price - tax;
	                    var incl = excl*(1+(this.currentTax/100));
	                } else {
	                    var tax = price * (this.defaultTax / 100);
	                    var excl = price;
	                    var incl = excl + tax;
	                }
	
	                excl += parseFloat(_plusDisposition);
	                incl += parseFloat(_plusDisposition);
	                excl -= parseFloat(_minusDisposition);
	                incl -= parseFloat(_minusDisposition);
	
	                //adding nontaxlable part of options
	                excl += parseFloat(nonTaxable);
	                
	                // NEW STUFF =========================================================================================
	                if (window.basePrice.product) {
		                var label = window.basePrice.product.getBasePriceLabel(incl, excl);
						var basepriceLabels = $$('.baseprice-label-' + this.productId);
                        if (pair.value.substring(0,9) == "old-price") basepriceLabels = $$('.baseprice-old-label-' + this.productId);
						if (basepriceLabels) basepriceLabels.each(function(element) {
		            		element.innerHTML = label;
		            	}.bind(this))
		            }
	                // END NEW STUFF =====================================================================================
	
	                if (pair.value == 'price-including-tax-'+this.productId) {
	                    price = incl;
	                } else if (pair.value == 'old-price-'+this.productId) {
	                    if (this.showIncludeTax || this.showBothPrices) {
	                        price = incl;
	                    } else {
	                        price = excl;
	                    }
	                } else {
	                    if (this.showIncludeTax) {
	                        price = incl;
	                    } else {
	                        if (!this.skipCalculate || _productPrice == 0) {
	                            price = excl;
	                        } else {
	                            price = optionPrices+parseFloat(_productPrice);
	                        }
	                    }
	                }
	
	                if (price < 0) price = 0;
	                formattedPrice = this.formatPrice(price);
	                if ($(pair.value).select('.price')[0]) {
	                    $(pair.value).select('.price')[0].innerHTML = formattedPrice;
	                    if ($(pair.value+this.duplicateIdSuffix) && $(pair.value+this.duplicateIdSuffix).select('.price')[0]) {
	                        $(pair.value+this.duplicateIdSuffix).select('.price')[0].innerHTML = formattedPrice;
	                    }
	                } else {
	                    $(pair.value).innerHTML = formattedPrice;
	                    if ($(pair.value+this.duplicateIdSuffix)) {
	                        $(pair.value+this.duplicateIdSuffix).innerHTML = formattedPrice;
	                    }
	                }
	            };
	        }.bind(this));
	    }
	});
	Product.OptionsPrice = BasePrice.OptionsPrice;
}
