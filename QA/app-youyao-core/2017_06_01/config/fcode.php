<?php
$config['channel'] = array(
    1 => 'COPY', //仅仅是通过复制链接!
    2 => 'LINE',
    3 => 'FACEBOOK',
    4 => 'WEIXIN'
);

$config['error'] = array(
    1 => '渠道参数异常',
    2 => '用户参数异常',
);
$config['type'] = array(
    1 => 'Fcode_TW'
);
$config['title'] = '好友邀請您入住特色民宿,送您300台幣優惠券';
$config['index_title'] = '邀请好友';
$config['url'] = 'http://m.vruan.dev.youyao.com/fcode/regis?';
$config['coupon'] = array(
    '-1' => array(  // 默认的
            array(
                'category' => 1,
                'num' => 1,
                'value' => 30,
            ),
            array(
                'category' => 2,
                'num' => 1,
                'value' => 50,
            ),
            array(
                'category' => 3,
                'num' => 1,
                'value' => 100,
            ),
    ),
    '10' => array(
            array(
                'category' => 4,
                'num' => 1,
                'value' => 60,
            ),
        ),
    '12' => array(
            array(
                'category' => 1,
                'num' => 1,
                'value' => 30,
            ),
            array(
                'category' => 2,
                'num' => 1,
                'value' => 50,
            ),
            array(
                'category' => 3,
                'num' => 1,
                'value' => 100,
            ),
    )
);
$config['point'] = array(
    '-1' => array( // 默认
            'source' => 'fcode_share',
            'value' => 20,
            'remark' => 'FCODE积分奖励',
        ),
    '10' => array(
            'source' => 'fcode_share',
            'value' => 60,
            'remark' => 'FCODE积分奖励',
        ),
    '12' => array(
            'source' => 'fcode_share',
            'value' => 20,
            'remark' => 'FCODE积分奖励',
        )
);
