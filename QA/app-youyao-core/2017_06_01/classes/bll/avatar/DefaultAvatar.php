<?php
class Bll_Avatar_DefaultAvatar {

	private $avatarlist;
    public function __construct() {
		$this->avatarlist = array(
			'018c29c499c2e96d3bcfe068fa1ab8f5a32580091062b0-EwF0Fe_fw658.jpg',
			'0566ec949b228424a444bbe0f8e9aed794fccff315c2c-l62810_fw658.jpg',
			'0ca422630ee67bf05cd1dd133bdb0428a292db2bb5f98-qn0Zoh_fw658.jpg',
			'122272284471416aa4da900cef91e727f239a0401f060-8kszBW_fw658.jpg',
			'1584005008d83b6d806c014665febafae755dc0937789-cRjjuD_fw658.jpg',
			'1628da4955d620df23b4071aff61b2853384715ff3fef-zL2mVm_fw658.jpg',
			'2a7c0d4c214e16c86a5e05dfe035aaa72140541216a13-W3NIbQ_fw658.jpg',
			'2c7e6652459a8b45bbcd54be02dca209af591be78395-ElsBmS_fw658.jpg',
			'2d478d45835c9d6683ba7976563b28b561691a7c13963-DUwhDH_fw658.jpg',
			'3b81f236f18e46d7bbf624369ca5465c81bfb348a8c77-dR0b0p_fw658.png',
			'3d54504aee3703f10883fa6f2e196b0483f8d6d278316-pCDdPs_fw658.jpg',
			'4b20b8303e26647640b5efee5508ef6ae9092687a6fad-ymhOFr_fw658.jpg',
			'5eedaf8f86edb1b886745bd2b75536755a898d6abe13-k40YZf_fw658.jpg',
			'63d85bb7d738466fc8b4e83d97f3a3a6e1fb47f3798a-bhNK32_fw658.jpg',
			'661232e63817e14a4d8c33d950d8e4699f83792441cef-GMs2mk_fw658.jpg',
			'6dec09e6eb3bee4ff137128fdafd3a49b901c09d1de17e-pjEgNF_fw658.jpg',
			'74481ddae7d86c7f64dca1fb9f403207cf237a1414bf5-jmwVIk_fw658.jpg',
			'7fdf8cc84f999d15befee42d3e1432554b246fe415685-wj1btk_fw658.jpg',
			'82cbd0c83bbac5e33274fda751d1a0d0305c3a75a87a-dTSqRK_fw658.jpg',
			'b07253f17a0cfb6e9738a7cec715428512dcc1b126ef2-HNpZsq_fw658.jpg',
			'b34c28278fdc319111379c17af57a196abdd043c1600a-ZY1NjU_fw658.jpg',
			'b40d2c5188c8bfe077d49526c0ad4fe01d97c72823421-i1mqog_fw658.jpg',
			'b512076cba296517702852b57365af12d084c6091948b-GLZ0Vv_fw658.jpg',
			'b956b639981e7acf31776e673d9bbd7f2dd2ee19b142-Whtf4b_fw658.jpg',
			'be636586ca40826c5eb7f401c7a562b52e6d7f59877d-n1oqM6_fw658.jpg',
			'cc557d5c5ce6ac70f9412ec5dcddf12743b764152d6c8-6mGAbb_fw658.jpg',
			'd8970fe63def424b882b66e1f9f47ea1e6b02d3b17377-8xWfdo_fw658.jpg',
			'dc0a2569441ed87d99d8398a5c0e21077b3612b44acbb-FulnVQ_fw658.jpg',
			'dc47d13633fcb913ba8632b25c4e445b4466212af1a7-Q6TEQX_fw658.jpg',
			'dcce75ee22b0130e558e2702ef03f06e182f6ad220c9b-4CoV46_fw658.jpg',
			'e642bbd2cf56107dbe67534900bfbc23582865b98c666-sTd3va_fw658.png',
			'e935e547deb6834e0c10c8a0a444f8de00f3f9a912017-lg0BWy_fw658.jpg',
			'ed293676853429978545b3b252d033d249813d6b5ab5a-FUCw3V_fw658.jpg',
			'effd6c65820509830efc4a270e516d91b7fffdeae70b0-ACmMl8_fw658.jpg',
			'f2470ae283800bd8e66fbd3993fbcdee706656fecf8c-qIIKdn_fw658.jpg',
			'f30df96a4de1f210e1a522a8f91c8dbe6eea41eff5b19-SOOcZJ_fw658.jpg',
			'f6651ef2ee823686c8d0f0a7fd8b118e4e3ab5cb1f33b-aXC4ii_fw658.jpg',
			'f9425ac4ab105c7617126dde6668644ec22b724426a40-Ho97NB_fw658.jpg',
			'fc878bb0d8203cce99a02062047156f04a11cec922390-DIenKe_fw658.jpg',
			'ffc8be4570331ba6957da29362a1d33ce9c9701b2e3c1-bVKqtS_fw658.jpg',
		);
    }

	function dispatch_avatar($uid) {
		$num = count($this->avatarlist);
		$key = $uid%$num;
		return $this->avatarlist[$key];
	}
}
