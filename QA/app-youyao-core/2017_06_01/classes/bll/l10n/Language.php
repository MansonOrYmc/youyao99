<?php

class Bll_L10n_Language {
	public function translate($source, $language='zh-hans') {
		$dao_l10n = new Dao_L10n_Language();
		$context = NULL;
		$textgroup = 'default';
		$result = $dao_l10n->get_translate_text($source, $language, $context, $textgroup);
		if ($result) {
			return $result['translation'];
		}
		else {
			return FALSE;
		}
	}
}