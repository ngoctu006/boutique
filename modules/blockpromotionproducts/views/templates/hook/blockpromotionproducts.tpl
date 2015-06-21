{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<!-- MODULE Block new products -->
<div id="promotion-products_block_right" class="block products_block">
	<h4 class="title_block st-title1">
            {*    	<a href="{$link->getPageLink('new-products')|escape:'html'}" title="{l s='New products' mod='blockproducts'}">{l s='New products' mod='blockproducts'}</a>
            *}    
            <span>{l s='Drop price' mod='blockpromotionproducts'}</span>
        </h4>
    <div class="block_content products-block">
        {if $products !== false}
            <ul class="products grid">
                {assign var='item' value=1}
                {foreach from=$products item=product name=myLoop}
                    <li {if $item % 3 == 0} class="nomargin" {/if}>
                        <a class="products-block-image" href="{$product.link|escape:'html'}" title="{$product.legend|escape:html:'UTF-8'}">
                            <img class="replace-2x img-responsive" src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html'}" alt="{$product.name|escape:html:'UTF-8'}" />
                        </a>
                        <div class="product-content">
                        	<h5>
                            	<a class="product-name" href="{$product.link|escape:'html'}" title="{$product.name|escape:html:'UTF-8'}">{$product.name|strip_tags|escape:html:'UTF-8'}</a>
                                  </h5>
                        	<p class="product-description">{$product.description_short|strip_tags:'UTF-8'|truncate:75:'...'}</p>
                            {if (!$PS_CATALOG_MODE AND ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
                            	{if isset($product.show_price) && $product.show_price && !isset($restricted_country_mode)}
                                    <div class="block-price-cart">
                                        <div class="price-box{if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0} block-drop-price {/if}">
                                            {if isset($product.specific_prices) && 
                                                $product.specific_prices && 
                                                isset($product.specific_prices.reduction) && 
                                                $product.specific_prices.reduction > 0}
						   {hook h="displayProductPriceBlock" product=$product type="old_price"}
						    <span class="old-price product-price">
							{displayWtPrice p=$product.price_without_reduction}
                                                    </span>
                                            {/if}
                                            <span class="price">
                                                    {if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
                                            </span>

                                        </div>
                                        <div class="button-container">
						{if ($product.id_product_attribute == 0 || 
                                                    (isset($add_prod_display) && ($add_prod_display == 1))) &&
                                                     $product.available_for_order && !isset($restricted_country_mode) && 
                                                     $product.customizable != 2 && !$PS_CATALOG_MODE}
							{if (!isset($product.customization_required) || 
                                                             !$product.customization_required) && 
                                                             ($product.allow_oosp || $product.quantity > 0)}
                                                                {capture}add=1&amp;id_product={$product.id_product|intval}{if isset($static_token)}&amp;token={$static_token}{/if}{/capture}
								<a class="button ajax_add_to_cart_button btn btn-default" href="{$link->getPageLink('cart', true, NULL, $smarty.capture.default, false)|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Add to cart'}" data-id-product="{$product.id_product|intval}" data-minimal_quantity="{if isset($product.product_attribute_minimal_quantity) && $product.product_attribute_minimal_quantity > 1}{$product.product_attribute_minimal_quantity|intval}{else}{$product.minimal_quantity|intval}{/if}">
									<span>{l s='Add to cart'}</span>
								</a>
							{else}
								<span class="button ajax_add_to_cart_button btn btn-default disabled">
									<span>{l s='Add to cart'}</span>
								</span>
							{/if}
						{/if}
					</div>    
                                    </div>
                                {/if}
                            {/if}
                        </div>
                    </li>
                    {$item = $item+1}
                {/foreach}
            </ul>
        {else}
        	<p>&raquo; {l s='Do not allow new products at this time.' mod='blockproducts'}</p>
        {/if}
    </div>
</div>
<!-- /MODULE Block new products -->