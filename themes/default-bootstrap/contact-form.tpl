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
{capture name=path}{l s='Contact'}{/capture}
<h1 class="page-heading  st-title1">
    <span>{l s='Contact us'}</span>
</h1>
<p>{l s="Contactez nous pour toutes demandes d'informations sur les produits, les paiements, la livraison..."}</p>
 <div class="wrap-discription">
    <div class="map">
        <img src="{$base_dir}/images/map.png">
    </div>
    <div class="discription-contact">
        <h5>Voici nos coordonnées : </h5>
          <p><span>Boutique Nounou ZA de Kervandras </span>
            <span>56250 Sulniac </span><br/>
            <span>Morbihan - France</span><br/>
            <span>info@boutique-nounou.com</span><br/>
            <span>Tel : 02.97.47.98.93 Port: 07.83.08.89.44</span>
        <p>
            <span>Ouvert sur rendez vous : du Mardi au Vendredi de 9h à 12h et de 14h à 18h <span><br />
            <span>Permanence téléphonique du lundi au Vendredi de 10h00 a 12h00 et de 14h00 a 17h00 au 02.97.47.98.93<span>
        </p>
    </div>
</div>   
{if isset($confirmation)}
	<p class="alert alert-success">{l s='Your message has been successfully sent to our team.'}</p>
	<ul class="footer_links clearfix">
		<li>
			<a class="btn btn-default button button-small" href="{$base_dir}">
				<span>
					<i class="icon-chevron-left"></i>{l s='Home'}
				</span>
			</a>
		</li>
	</ul>
{elseif isset($alreadySent)}
	<p class="alert alert-warning">{l s='Your message has already been sent.'}</p>
	<ul class="footer_links clearfix">
		<li>
			<a class="btn btn-default button button-small" href="{$base_dir}">
				<span>
					<i class="icon-chevron-left"></i>{l s='Home'}
				</span>
			</a>
		</li>
	</ul>
{else}
    <h1 class="page-heading  st-title1">
         <span>{l s='Contact us'}</span>
    </h1>
	{include file="$tpl_dir./errors.tpl"}
	<form action="{$request_uri}" method="post" class="contact-form-box" enctype="multipart/form-data">
		<fieldset>
			<div class="clearfix">
				<div class="col-xs-12 col-md-3">
					
					<p class="form-group">
						<label for="nom">{l s='Nom'}</label>
					        <input class="form-control" type="text" id="nom" name="nom" value="{if $nom}{$nom}{/if}" />
					</p>
					<p class="form-group">
						<label for="prenom">{l s='Prénom'}</label>
					        <input class="form-control" type="text" id="prenom" name="prenom" value="{if $prenom}{$prenom}{/if}" />
					</p>
					<p class="form-group">
						<label for="email">{l s='Email address'}</label>
						{if isset($customerThread.email)}
							<input class="form-control grey" type="text" id="email" name="from" value="{$customerThread.email|escape:'html':'UTF-8'}" readonly="readonly" />
						{else}
							<input class="form-control grey validate" type="text" id="email" name="from" data-validate="isEmail" value="{$email|escape:'html':'UTF-8'}" />
						{/if}
					</p>
					<p class="form-group">
						<label for="telephone">{l s='Téléphone'}</label>
					        <input class="form-control" type="text" id="telephone" name="telephone" value="{if $telephone}{$telephone}{/if}" />
					</p>
					<p class="form-group">
						<label for="votre-message">{l s="Quel est l'objet de votre message"}</label>
					        <input class="form-control" type="text"  name="votre-message" value="{if $votre_message}{$votre_message}{/if}" />

					</p>
				</div>
				<div class="col-xs-12 col-md-9">
					<div class="form-group">
						<label for="message">{l s='Quel ét votre message'}</label>
						<textarea class="form-control" id="message" name="message">{if isset($message)}{$message|escape:'html':'UTF-8'|stripslashes}{/if}</textarea>
					</div>
				</div>
			</div>
			<div class="form-group">
                            <label></label>
				<button type="submit" name="submitMessage" id="submitMessage" class=""><span>{l s='Valider'}</span></button>
			</div>
		</fieldset>
	</form>
{/if}
{addJsDefL name='contact_fileDefaultHtml'}{l s='No file selected' js=1}{/addJsDefL}
{addJsDefL name='contact_fileButtonHtml'}{l s='Choose File' js=1}{/addJsDefL}
