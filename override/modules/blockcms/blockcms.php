<?php
class BlockCmsOverride extends BlockCms
{
	

	public function hookHeader($params)
	{
		$this->context->controller->addCSS(($this->_path).'blockcms.css', 'all');
	}

	public function hookLeftColumn()
	{
		return $this->displayBlockCMS(BlockCMSModel::LEFT_COLUMN);
	}

	public function hookRightColumn()
	{
		return $this->displayBlockCMS(BlockCMSModel::RIGHT_COLUMN);
	}

	public function hookFooter()
	{
		if (!($block_activation = Configuration::get('FOOTER_BLOCK_ACTIVATION')))
			return;

		if (!$this->isCached('blockcms.tpl', $this->getCacheId(BlockCMSModel::FOOTER)))
		{
			$display_poweredby = Configuration::get('FOOTER_POWEREDBY');
			$this->smarty->assign(
				array(
					'block' => 0,
					'contact_url' => 'contact',
					'cmslinks' => BlockCMSModel::getCMSTitlesFooter(),
					'display_stores_footer' => Configuration::get('PS_STORES_DISPLAY_FOOTER'),
					'display_poweredby' => ((int)$display_poweredby === 1 || $display_poweredby === false),
					'footer_text' => Configuration::get('FOOTER_CMS_TEXT_'.(int)$this->context->language->id),
					'show_price_drop' => Configuration::get('FOOTER_PRICE-DROP'),
					'show_new_products' => Configuration::get('FOOTER_NEW-PRODUCTS'),
					'show_best_sales' => Configuration::get('FOOTER_BEST-SALES'),
					'show_contact' => Configuration::get('FOOTER_CONTACT'),
					'show_sitemap' => Configuration::get('FOOTER_SITEMAP')
				)
			);
		}
		return $this->display(__FILE__, 'blockcms.tpl', $this->getCacheId(BlockCMSModel::FOOTER));
	}

	protected function updatePositionsDnd()
	{
		if (Tools::getValue('cms_block_0'))
			$positions = Tools::getValue('cms_block_0');
		elseif (Tools::getValue('cms_block_1'))
			$positions = Tools::getValue('cms_block_1');
		else
			$positions = array();

		foreach ($positions as $position => $value)
		{
			$pos = explode('_', $value);

			if (isset($pos[2]))
				BlockCMSModel::updateCMSBlockPosition($pos[2], $position);
		}
	}
	public function hookActionShopDataDuplication($params)
	{
		//get all cmd block to duplicate in new shop
		$cms_blocks = Db::getInstance()->executeS('
			SELECT * FROM `'._DB_PREFIX_.'cms_block` cb
			JOIN `'._DB_PREFIX_.'cms_block_shop` cbf
				ON (cb.`id_cms_block` = cbf.`id_cms_block` AND cbf.`id_shop` = '.(int)$params['old_id_shop'].') ');

		if (count($cms_blocks))
		{
			foreach ($cms_blocks as $cms_block)
			{
				Db::getInstance()->execute('
					INSERT IGNORE INTO '._DB_PREFIX_.'cms_block (`id_cms_block`, `id_cms_category`, `location`, `position`, `display_store`)
					VALUES (NULL, '.(int)$cms_block['id_cms_category'].', '.(int)$cms_block['location'].', '.(int)$cms_block['position'].', '.(int)$cms_block['display_store'].');');

				$id_block_cms =  Db::getInstance()->Insert_ID();

				Db::getInstance()->execute('INSERT IGNORE INTO '._DB_PREFIX_.'cms_block_shop (`id_cms_block`, `id_shop`) VALUES ('.(int)$id_block_cms.', '.(int)$params['new_id_shop'].');');

				$langs = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'cms_block_lang` WHERE `id_cms_block` = '.(int)$cms_block['id_cms_block']);

				foreach($langs as $lang)
					Db::getInstance()->execute('
						INSERT IGNORE INTO `'._DB_PREFIX_.'cms_block_lang` (`id_cms_block`, `id_lang`, `name`)
						VALUES ('.(int)$id_block_cms.', '.(int)$lang['id_lang'].', \''.pSQL($lang['name']).'\');');

				$pages =  Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'cms_block_page` WHERE `id_cms_block` = '.(int)$cms_block['id_cms_block']);

				foreach($pages as $page)
					Db::getInstance()->execute('
						INSERT IGNORE INTO `'._DB_PREFIX_.'cms_block_page` (`id_cms_block_page`, `id_cms_block`, `id_cms`, `is_category`)
						VALUES (NULL, '.(int)$id_block_cms.', '.(int)$page['id_cms'].', '.(int)$page['is_category'].');');
			}
		}
	}
}
