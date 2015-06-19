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
{if isset($products) && $products}
	<!-- Products list -->
	<ul{if isset($id) && $id} id="{$id}"{/if} class="product_list products row grid{if isset($class) && $class} {$class}{/if}">
            {assign var='item' value=1}
            {foreach from=$products item=product name=products}
                <li {if $item % 3 == 0} class="nomargin" {/if}>
                            <a class="products-block-image" href="{$product.link|escape:'html'}" title="{$product.legend|escape:html:'UTF-8'}">
                                <img class="replace-2x img-responsive" src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'small_default')|escape:'html'}" alt="{$product.name|escape:html:'UTF-8'}" />
                            </a>
                            <div class="product-content">
                                <h5>
                                    <a class="product-name" href="{$product.link|escape:'html'}" title="{$product.name|escape:html:'UTF-8'}">{$product.name|strip_tags|escape:html:'UTF-8'}</a>
                                </h5>
                                <p class="product-description">{$product.description_short|strip_tags:'UTF-8'|truncate:75:'...'}</p>
                                {if (!$PS_CATALOG_MODE AND ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
                                    {if isset($product.show_price) && $product.show_price && !isset($restricted_country_mode)}
                                        <div class="block-price-cart">
                                            <div class="price-box">
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
            {/foreach}
	</ul>
{/if}
