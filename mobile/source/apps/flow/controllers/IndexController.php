<?php
//dezend by  QQ:2172298892
namespace apps\flow\controllers;

class IndexController extends \apps\base\controllers\FrontendController
{
	private $sess_id = '';
	private $a_sess = '';
	private $b_sess = '';
	private $c_sess = '';
	private $sess_ip = '';
	private $region_id = 0;
	private $area_id = 0;

	public function __construct()
	{
		parent::__construct();
		l(require ROOT_PATH . 'source/language/' . c('shop.lang') . '/flow.php');
		l(require ROOT_PATH . 'source/language/' . c('shop.lang') . '/user.php');
		$files = array('order', 'clips', 'transaction');
		$this->load_helper($files);
		$this->check_login();

		if (!empty($_SESSION['user_id'])) {
			$this->sess_id = ' user_id = \'' . $_SESSION['user_id'] . '\' ';
			$this->a_sess = ' a.user_id = \'' . $_SESSION['user_id'] . '\' ';
			$this->b_sess = ' b.user_id = \'' . $_SESSION['user_id'] . '\' ';
			$this->c_sess = ' c.user_id = \'' . $_SESSION['user_id'] . '\' ';
			$this->sess_ip = '';
		}
		else {
			$this->sess_id = ' session_id = \'' . real_cart_mac_ip() . '\' ';
			$this->a_sess = ' a.session_id = \'' . real_cart_mac_ip() . '\' ';
			$this->b_sess = ' b.session_id = \'' . real_cart_mac_ip() . '\' ';
			$this->c_sess = ' c.session_id = \'' . real_cart_mac_ip() . '\' ';
			$this->sess_ip = real_cart_mac_ip();
		}

		$area_info = get_area_info($this->province_id);
		$this->area_id = $area_info['region_id'];
		$where = 'regionId = \'' . $this->province_id . '\'';
		$date = array('parent_id');
		$this->region_id = get_table_date('region_warehouse', $where, $date, 2);
		if (isset($_COOKIE['region_id']) && !empty($_COOKIE['region_id'])) {
			$this->region_id = $_COOKIE['region_id'];
		}
	}

	private function showCoupons($attr)
	{
		$user_id = $_SESSION['user_id'];
		$arr = array();
		$sql = ' select * from  {pre}coupons_user cu left join {pre}coupons cs  on cu.cou_id = cs.cou_id  where cu.is_use = 0  and  cu.user_id=\'' . $user_id . '\' ';
		$res = $this->db->getAll($sql);

		foreach ($res as $i) {
			$goodsid = $i['cou_goods'];

			if (empty($goodsid)) {
				$arr[] = $i['cou_id'];
			}
			else {
				$gs = explode(',', $goodsid);

				foreach ($gs as $k) {
					foreach ($attr as $j) {
						if ($j['goods_id'] == $k) {
							$arr[] = $i['cou_id'];
						}
					}
				}
			}
		}

		return array_unique($arr);
	}

	public function actionIndex()
	{
		$flow_type = (isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS);
		$_SESSION['shipping_type'] = 0;
		$_SESSION['shipping_type_ru_id'] = array();
		$_SESSION['flow_consignee']['point_id'] = array();
		$direct_shopping = (isset($_REQUEST['direct_shopping']) ? intval($_REQUEST['direct_shopping']) : $_SESSION['direct_shopping']);
		$cart_value = (isset($_REQUEST['cart_value']) ? htmlspecialchars($_REQUEST['cart_value']) : $_SESSION['cart_value']);

		if (empty($cart_value)) {
			$cart_value = get_cart_value($flow_type);
		}

		if (empty($cart_value) && !empty($_SESSION['cart_value'])) {
			$cart_value = $_SESSION['cart_value'];
		}

		$_SESSION['cart_value'] = $cart_value;

		if ($flow_type == CART_GROUP_BUY_GOODS) {
			$this->assign('is_group_buy', 1);
		}
		else if ($flow_type == CART_EXCHANGE_GOODS) {
			$this->assign('is_exchange_goods', 1);
		}
		else if ($flow_type == CART_PRESALE_GOODS) {
			$this->assign('is_presale_goods', 1);
		}
		else {
			$_SESSION['flow_order']['extension_code'] = '';
		}

		$sql = 'SELECT COUNT(*) FROM {pre}cart  WHERE ' . $this->sess_id . 'AND parent_id = 0 AND is_gift = 0 AND rec_type = \'' . $flow_type . '\'';

		if ($this->db->getOne($sql) == 0) {
			show_message(l('no_goods_in_cart'), '', u('site/index/index'), 'warning');
		}

		if (empty($direct_shopping) && ($_SESSION['user_id'] == 0)) {
			ecs_header('Location: ' . u('user/login/index'));
			exit();
		}

		$consignee = get_consignee($_SESSION['user_id']);

		if (!check_consignee_info($consignee, $flow_type)) {
			ecs_header('Location: ' . u('address_list'));
			exit();
		}

		$user_address = get_order_user_address_list($_SESSION['user_id']);
		if (($direct_shopping != 1) && !empty($_SESSION['user_id'])) {
			$_SESSION['browse_trace'] = u('cart/index/index');
		}
		else {
			$_SESSION['browse_trace'] = u('flow/index/index');
		}

		if ((count($user_address) <= 0) && ($direct_shopping != 1)) {
			ecs_header('Location: ' . u('address_list'));
			exit();
		}

		if ($consignee) {
			$consignee['province_name'] = get_goods_region_name($consignee['province']);
			$consignee['city_name'] = get_goods_region_name($consignee['city']);
			$consignee['district_name'] = get_goods_region_name($consignee['district']);
			$consignee['region'] = $consignee['province_name'] . '&nbsp;' . $consignee['city_name'] . '&nbsp;' . $consignee['district_name'];
		}

		$default_id = $this->db->getOne('SELECT address_id FROM {pre}users WHERE user_id=\'' . $_SESSION['user_id'] . '\'');

		if ($consignee['address_id'] == $default_id) {
			$this->assign('is_default', '1');
		}

		$_SESSION['flow_consignee'] = $consignee;
		$this->assign('consignee', $consignee);
		$cart_goods_list = cart_goods($flow_type, $cart_value, 1, $this->region_id, $this->area_id);

		if (empty($cart_goods_list)) {
			$this->redirect(u('cart/index/index'));
		}

		if ($cart_goods_list) {
			foreach ($cart_goods_list as $key => $val) {
				$amount = 0;
				$goods_price_amount = 0;
				$amount += $val['shipping']['shipping_fee'];

				foreach ($val['goods_list'] as $v) {
					$amount += $v['subtotal'];
					$goods_price_amount += $v['subtotal'];
				}

				$cart_goods_list[$key]['amount'] = $amount ? price_format($amount, false) : 0;
				$cart_goods_list[$key]['goods_price_amount'] = $goods_price_amount ? price_format($goods_price_amount, false) : 0;
			}
		}

		$cart_goods_list_new = cart_by_favourable($cart_goods_list);
		$this->assign('goods_list', $cart_goods_list_new);
		$cart_goods = cart_goods($flow_type, $cart_value);
		if (($flow_type != CART_GENERAL_GOODS) || (c('shop.one_step_buy') == '1')) {
			$this->assign('allow_edit_cart', 0);
		}
		else {
			$this->assign('allow_edit_cart', 1);
		}

		$this->assign('config', c('shop'));
		$order = flow_order_info();
		$this->assign('order', $order);
		if (($flow_type != CART_EXCHANGE_GOODS) && ($flow_type != CART_GROUP_BUY_GOODS)) {
			$discount = compute_discount(3, $cart_value);
			$this->assign('discount', $discount['discount']);
			$favour_name = (empty($discount['name']) ? '' : join(',', $discount['name']));
			$this->assign('your_discount', sprintf(l('your_discount'), $favour_name, price_format($discount['discount'])));
		}

		$total = order_fee($order, $cart_goods, $consignee, 0, $cart_value, 0, $cart_goods_list);
		$user_coupons = $this->get_user_coupons_list($_SESSION['user_id'], true, $total['goods_price'], $cart_goods);

		if (!empty($user_coupons)) {
			foreach ($user_coupons as $k => $v) {
				$user_coupons[$k]['cou_end_time'] = local_date('Y-m-d', $v['cou_end_time']);
				$user_coupons[$k]['cou_type'] = $v['cou_type'] == 3 ? '全场券' : ($v['cou_type'] == 4 ? '会员券' : ($v['cou_type'] == 2 ? '购物券' : ($v['cou_type'] == 1 ? '注册券' : '未知')));
				$user_coupons[$k]['cou_goods_name'] = $v['cou_goods'] ? '限商品' : '全品类通用';
			}

			$this->assign('user_coupons', $user_coupons);
		}

		$this->assign('total', $total);
		$this->assign('shopping_money', sprintf(l('shopping_money'), $total['formated_goods_price']));
		$this->assign('market_price_desc', sprintf(l('than_market_price'), $total['formated_market_price'], $total['formated_saving'], $total['save_rate']));
		$days = array();
		$shipping_date_list = $this->db->getAll('SELECT * FROM ' . $this->ecs->table('shipping_date'));
		$shipping_date = array();

		for ($i = 0; $i <= 6; $i++) {
			$year = date('Y-m-d', strtotime(' +' . $i . 'day'));
			$date = date('m月d日', strtotime(' +' . $i . 'day'));
			$shipping_date[$i]['id'] = $i;
			$shipping_date[$i]['name'] = $date . '【周' . transition_date($year) . '】';

			if ($shipping_date_list) {
				foreach ($shipping_date_list as $key => $val) {
					$strtime = strtotime($year . ' ' . $val['end_date']);
					if (($val['select_day'] <= $i) && ((gmtime() + (8 * 3600)) <= $strtime)) {
						$shipping_date[$i]['child'][$key]['id'] = $val['shipping_date_id'];
						$shipping_date[$i]['child'][$key]['name'] = $val['start_date'] . '-' . $val['end_date'];
					}
				}
			}
		}

		$this->assign('shipping_date', json_encode($shipping_date));
		$district = $_SESSION['flow_consignee']['district'];
		$city = $_SESSION['flow_consignee']['city'];
		$sql = 'SELECT * FROM ' . $this->ecs->table('region') . ' WHERE parent_id = \'' . $city . '\'';
		$district_list = $this->db->getAll($sql);
		$picksite_list = get_self_point($district);
		$this->assign('picksite_list', $picksite_list);
		$this->assign('district_list', $district_list);
		$this->assign('district', $district);
		$this->assign('city', $city);

		if ($order['shipping_id'] == 0) {
			$cod = true;
			$cod_fee = 0;
		}
		else {
			$shipping = shipping_info($order['shipping_id']);
			$cod = $shipping['support_cod'];

			if ($cod) {
				if ($flow_type == CART_GROUP_BUY_GOODS) {
					$group_buy_id = $_SESSION['extension_id'];

					if ($group_buy_id <= 0) {
						show_message('error group_buy_id');
					}

					$group_buy = group_buy_info($group_buy_id);

					if (empty($group_buy)) {
						show_message('group buy not exists: ' . $group_buy_id);
					}

					if (0 < $group_buy['deposit']) {
						$cod = false;
						$cod_fee = 0;
						$this->assign('gb_deposit', $group_buy['deposit']);
					}
				}

				if ($cod) {
					$shipping_area_info = shipping_area_info($order['shipping_id'], $region);
					$cod_fee = $shipping_area_info['pay_fee'];
				}
			}
			else {
				$cod_fee = 0;
			}
		}

		$payment_list = available_payment_list(1, $cod_fee);

		if (isset($payment_list)) {
			foreach ($payment_list as $key => $payment) {
				if (substr($payment['pay_code'], 0, 4) == 'pay_') {
					unset($payment_list[$key]);
					continue;
				}

				if ($payment['is_cod'] == '1') {
					$payment_list[$key]['format_pay_fee'] = '<span id="ECS_CODFEE">' . $payment['format_pay_fee'] . '</span>';
				}

				if (($payment['pay_code'] == 'yeepayszx') && (300 < $total['amount'])) {
					unset($payment_list[$key]);
				}

				if (!file_exists(ROOT_PATH . 'source/apps/wechat')) {
					if ($payment['pay_code'] == 'wxpay') {
						unset($payment_list[$key]);
					}
				}

				if ($payment['pay_code'] == 'balance') {
					if ($_SESSION['user_id'] == 0) {
						unset($payment_list[$key]);
					}
					else if ($_SESSION['flow_order']['pay_id'] == $payment['pay_id']) {
						$this->assign('disable_surplus', 1);
					}
				}

				if (!file_exists(ADDONS_PATH . 'payment/' . $payment['pay_code'] . '.php')) {
					unset($payment_list[$key]);
				}
			}
		}

		$this->assign('payment_list', $payment_list);

		if ($order['pay_id']) {
			$payment_selected = payment_info($order['pay_id']);

			if (file_exists(ADDONS_PATH . 'payment/' . $payment_selected['pay_code'] . '.php')) {
				$payment_selected['format_pay_fee'] = strpos($payment_selected['pay_fee'], '%') !== false ? $payment_selected['pay_fee'] : price_format($payment_selected['pay_fee'], false);
				$this->assign('payment_selected', $payment_selected);
			}
		}

		if (0 < $total['real_goods_count']) {
			$use_package = c('shop.use_package');
			if (!isset($use_package) || ($use_package == '1')) {
				$pack_list = pack_list();
				$this->assign('pack_list', $pack_list);
			}

			$pack_info = ($order['pack_id'] ? pack_info($order['pack_id']) : array());
			$pack_info['format_pack_fee'] = price_format($pack_info['pack_fee'], false);
			$pack_info['format_free_money'] = price_format($pack_info['free_money'], false);
			$this->assign('pack_info', $pack_info);
			$use_card = c('shop.use_card');
			if (!isset($use_card) || ($use_card == '1')) {
				$this->assign('card_list', card_list());
			}
		}

		$user_info = user_info($_SESSION['user_id']);
		$use_surplus = c('shop.use_surplus');
		if ((!isset($use_surplus) || ($use_surplus == '1')) && (0 < $_SESSION['user_id']) && (0 < $user_info['user_money'])) {
			$this->assign('allow_use_surplus', 1);
			$this->assign('your_surplus', $user_info['user_money']);
		}

		$use_integral = c('shop.use_integral');
		if ((!isset($use_integral) || ($use_integral == '1')) && (0 < $_SESSION['user_id']) && (0 < $user_info['pay_points']) && ($flow_type != CART_GROUP_BUY_GOODS) && ($flow_type != CART_EXCHANGE_GOODS)) {
			$order_max_integral = flow_available_points($cart_value);
			$this->assign('allow_use_integral', 1);
			$this->assign('order_max_integral', $order_max_integral);
			$this->assign('your_integral', $user_info['pay_points']);
			$integral_scale = c('shop.integral_scale');
			$integral_scale = ($integral_scale ? $integral_scale / 100 : 0);
			$integral_money = $order_max_integral * $integral_scale;
			$this->assign('integral_money', $integral_money);
			$this->assign('integral_money_format', price_format($integral_money, false));
		}

		$use_bonus = c('shop.use_bonus');
		if ((!isset($use_bonus) || ($use_bonus == '1')) && ($flow_type != CART_GROUP_BUY_GOODS) && ($flow_type != CART_EXCHANGE_GOODS)) {
			$user_bonus = user_bonus($_SESSION['user_id'], $total['goods_price'], $cart_value);

			if (!empty($user_bonus)) {
				foreach ($user_bonus as $key => $val) {
					$user_bonus[$key]['type_money'] = round($val['type_money']);
					$user_bonus[$key]['bonus_money_formated'] = price_format($val['type_money'], false);
					$user_bonus[$key]['use_start_date'] = local_date('Y-m-d', $val['use_start_date']);
					$user_bonus[$key]['use_end_date'] = local_date('Y-m-d', $val['use_end_date']);

					if ($val['usebonus_type'] == 1) {
						$user_bonus[$key]['shop_name'] = '全场通用';
					}
					else if ($val['user_id'] == 0) {
						$user_bonus[$key]['shop_name'] = '';
					}
					else {
						$user_bonus[$key]['shop_name'] = get_shop_name($val['user_id'], 1);
					}
				}

				$this->assign('bonus_num', count($user_bonus));
				$this->assign('bonus_list', $user_bonus);
			}

			if ($order['bonus_id']) {
				$order_bonus = bonus_info($order['bonus_id']);
				$order_bonus['type_money_format'] = price_format($order_bonus['type_money'], false);
				$this->assign('order_bonus', $order_bonus);
			}

			$this->assign('allow_use_bonus', 1);
		}

		$use_how_oos = c('shop.use_how_oos');
		if (!isset($use_how_oos) || ($use_how_oos == '1')) {
			$oos = l('oos');
			if (is_array($oos) && !empty($oos)) {
				$this->assign('how_oos_list', $GLOBALS['_LANG']['oos']);
			}
		}

		$can_invoice = c('shop.can_invoice');
		if ((!isset($can_invoice) || ($can_invoice == '1')) && isset($GLOBALS['_CFG']['invoice_content']) && (trim($GLOBALS['_CFG']['invoice_content']) != '') && ($flow_type != CART_EXCHANGE_GOODS)) {
			$inv_content_list = explode("\n", str_replace("\r", '', $GLOBALS['_CFG']['invoice_content']));
			$this->assign('inv_content_list', $inv_content_list);
			$inv_type_list = array();
			$invoice_type = c('shop.invoice_type');

			foreach ($invoice_type['type'] as $key => $type) {
				if (!empty($type)) {
					$inv_type_list[$type] = $type . ' [' . floatval($GLOBALS['_CFG']['invoice_type']['rate'][$key]) . '%]';
				}
			}

			$this->assign('inv_type_list', $inv_type_list);
			$invoice_type = c('shop.invoice_type');
			$order['need_inv'] = 1;
			$order['inv_type'] = $invoice_type['type'][0];
			$order['inv_payee'] = '个人';
			$order['inv_content'] = $inv_content_list[0];
		}

		$_SESSION['flow_order'] = $order;
		$this->assign('order', $order);
		$this->assign('page_title', '订单确认');
		$this->display('checkout');
	}

	public function actiondone()
	{
		$flow_type = (isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS);
		$sql = 'SELECT COUNT(*) FROM {pre}cart WHERE ' . $this->sess_id . 'AND parent_id = 0 AND is_gift = 0 AND rec_type = \'' . $flow_type . '\' AND rec_id ' . db_create_in($_SESSION['cart_value']) . ' ';

		if ($this->db->getOne($sql) == 0) {
			show_message(l('no_goods_in_cart'), '', u('cart/index/index'), 'warning');
		}

		if ((c('shop.use_storage') == '1') && (c('shop.stock_dec_time') == SDT_PLACE)) {
			$cart_goods_stock = get_cart_goods($_SESSION['cart_value']);
			$_cart_goods_stock = array();

			if (!empty($cart_goods_stock['goods_list'])) {
				foreach ($cart_goods_stock['goods_list'] as $value) {
					foreach ($value['goods_list'] as $value2) {
						$_cart_goods_stock[$value2['rec_id']] = $value2['goods_number'];
					}
				}

				flow_cart_stock($_cart_goods_stock);
				unset($cart_goods_stock);
				unset($_cart_goods_stock);
			}
		}

		if (empty($_SESSION['direct_shopping']) && ($_SESSION['user_id'] == 0)) {
			ecs_header('Location: ' . u('user/login/index'));
			exit();
		}

		$consignee = get_consignee($_SESSION['user_id']);

		if (!check_consignee_info($consignee, $flow_type)) {
			ecs_header('Location: ' . u('address_list'));
			exit();
		}

		$where_flow = '';
		$_POST['how_oos'] = isset($_POST['how_oos']) ? intval($_POST['how_oos']) : 0;
		$_POST['card_message'] = isset($_POST['card_message']) ? compile_str($_POST['card_message']) : '';
		$_POST['inv_type'] = !empty($_POST['inv_type']) ? compile_str($_POST['inv_type']) : '';
		$_POST['inv_payee'] = isset($_POST['inv_payee']) ? compile_str($_POST['inv_payee']) : '';
		$_POST['inv_content'] = isset($_POST['inv_content']) ? compile_str($_POST['inv_content']) : '';
		$msg = i('post.postscript', '', 'trim');
		$ru_id_arr = i('post.ru_id');
		$shipping_arr = i('post.shipping');
		$postscript = '';

		if (1 < count($msg)) {
			$postscript = array();

			foreach ($msg as $k => $v) {
				$postscript[$ru_id_arr[$k]] = $v;
			}
		}
		else {
			$postscript = (isset($msg[0]) ? $msg[0] : '');
		}

		$shipping_type = i('post.shipping_type');
		$shipping = get_order_post_shipping($shipping_arr, $ru_id_arr);
		$point = i('post.point_id', '');

		if (is_array($point)) {
			$point = array_filter($point);
		}

		$point_id = '';
		$shipping_dateStr = '';
		if (is_array($point) && !empty($point)) {
			foreach (i('post.point_id') as $key => $val) {
				if ($shipping_type[$key] == 1) {
					$point_id .= $key . '|' . $val . ',';
				}
			}

			if (is_array(i('post.shipping_dateStr'))) {
				$shipping_dateStr = '';

				foreach (i('post.shipping_dateStr') as $key => $val) {
					if ($shipping_type[$key] == 1) {
						$shipping_dateStr .= $key . '|' . $val . ',';
					}
				}

				if ($point_id && $shipping_dateStr) {
					$point_id = substr($point_id, 0, -1);
					$shipping_dateStr = substr($shipping_dateStr, 0, -1);
				}
			}
		}

		if (count($_POST['shipping']) == 1) {
			$shipping['shipping_id'] = $shipping_arr[0];

			if (is_array($point)) {
				foreach (i('post.point_id') as $key => $val) {
					if ($shipping_type[$key] == 1) {
						$point_id = $val;
					}
				}
			}
			else {
				$point_id = $point;
			}

			if (is_array(i('post.shipping_dateStr'))) {
				foreach (i('post.shipping_dateStr') as $key => $val) {
					if ($shipping_type[$key] == 1) {
						$shipping_dateStr = $val;
					}
				}
			}
			else {
				$shipping_dateStr = i('post.shipping_dateStr');
			}
		}

		$order = array('shipping_id' => $shipping['shipping_id'], 'pay_id' => intval($_POST['payment']), 'pack_id' => isset($_POST['pack']) ? intval($_POST['pack']) : 0, 'card_id' => isset($_POST['card']) ? intval($_POST['card']) : 0, 'card_message' => trim($_POST['card_message']), 'surplus' => isset($_POST['surplus']) ? floatval($_POST['surplus']) : 0, 'integral' => isset($_POST['integral']) ? intval($_POST['integral']) : 0, 'bonus_id' => isset($_POST['bonus']) ? intval($_POST['bonus']) : 0, 'need_inv' => empty($_POST['need_inv']) ? 0 : 1, 'inv_type' => i('inv_type'), 'inv_payee' => trim($_POST['inv_payee']), 'inv_content' => trim($_POST['inv_content']), 'postscript' => is_array($postscript) ? '' : $postscript, 'how_oos' => isset($GLOBALS['LANG']['oos'][$_POST['how_oos']]) ? addslashes($GLOBALS['LANG']['oos'][$_POST['how_oos']]) : '', 'need_insure' => isset($_POST['need_insure']) ? intval($_POST['need_insure']) : 0, 'user_id' => $_SESSION['user_id'], 'add_time' => gmtime(), 'order_status' => OS_UNCONFIRMED, 'shipping_status' => SS_UNSHIPPED, 'pay_status' => PS_UNPAYED, 'agency_id' => get_agency_by_regions(array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district'])), 'point_id' => $point_id, 'shipping_dateStr' => $shipping_dateStr, 'cou_id' => $_POST['cou_id']);
		if ((empty($order['shipping_id']) && empty($point_id)) || empty($order['pay_id'])) {
			show_message('请选择配送方式或者支付方式');
		}

		if (isset($_SESSION['flow_type']) && (intval($_SESSION['flow_type']) != CART_GENERAL_GOODS)) {
			$order['extension_code'] = $_SESSION['extension_code'];
			$order['extension_id'] = $_SESSION['extension_id'];
		}
		else {
			$order['extension_code'] = '';
			$order['extension_id'] = 0;
		}

		$user_id = $_SESSION['user_id'];

		if (0 < $user_id) {
			$user_info = user_info($user_id);
			$order['surplus'] = min($order['surplus'], $user_info['user_money'] + $user_info['credit_line']);

			if ($order['surplus'] < 0) {
				$order['surplus'] = 0;
			}

			$flow_points = flow_available_points($_SESSION['cart_value']);
			$user_points = $user_info['pay_points'];
			$order['integral'] = min($order['integral'], $user_points, $flow_points);

			if ($order['integral'] < 0) {
				$order['integral'] = 0;
			}
		}
		else {
			$order['surplus'] = 0;
			$order['integral'] = 0;
		}

		if (0 < $order['bonus_id']) {
			$bonus = bonus_info($order['bonus_id']);
			if (empty($bonus) || ($bonus['user_id'] != $user_id) || (0 < $bonus['order_id']) || (cart_amount(true, $flow_type) < $bonus['min_goods_amount'])) {
				$order['bonus_id'] = 0;
			}
		}
		else if (isset($_POST['bonus_sn'])) {
			$bonus_sn = trim($_POST['bonus_sn']);
			$bonus = bonus_info(0, $bonus_sn);
			$now = gmtime();
			if (empty($bonus) || (0 < $bonus['user_id']) || (0 < $bonus['order_id']) || (cart_amount(true, $flow_type) < $bonus['min_goods_amount']) || ($bonus['use_end_date'] < $now)) {
			}
			else {
				if (0 < $user_id) {
					$sql = 'UPDATE {pre}user_bonus  SET user_id = \'' . $user_id . '\' WHERE bonus_id = \'' . $bonus['bonus_id'] . '\' LIMIT 1';
					$this->db->query($sql);
				}

				$order['bonus_id'] = $bonus['bonus_id'];
				$order['bonus_sn'] = $bonus_sn;
			}
		}

		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1, $this->region_id, $this->area_id);
		$cart_goods = cart_goods($flow_type, $_SESSION['cart_value']);

		if (empty($cart_goods)) {
			show_message(l('no_goods_in_cart'), l('back_home'), './', 'warning');
		}

		if (($flow_type == CART_GENERAL_GOODS) && (cart_amount(true, CART_GENERAL_GOODS) < c('shop.min_goods_amount'))) {
			show_message(sprintf(l('goods_amount_not_enough'), price_format(c('shop.min_goods_amount'), false)));
		}

		foreach ($consignee as $key => $value) {
			if (!is_array($value)) {
				if ($key != 'shipping_dateStr') {
					$order[$key] = addslashes($value);
				}
				else {
					$order[$key] = addslashes($order['shipping_dateStr']);
				}
			}
		}

		foreach ($cart_goods as $val) {
			if ($val['is_real']) {
				$is_real_good = 1;
			}
		}

		foreach ($cart_goods_list as $key => $val) {
			foreach ($_POST['ru_id'] as $kk => $vv) {
				if ($val['ru_id'] == $vv) {
					$cart_goods_list[$key]['tmp_shipping_id'] = $_POST['shipping'][$kk];
					continue;
				}
			}
		}

		$pay_type = 0;
		$total = order_fee($order, $cart_goods, $consignee, 1, $_SESSION['cart_value'], $pay_type, $cart_goods_list);
		$order['bonus'] = $total['bonus'];
		$order['goods_amount'] = $total['goods_price'];
		$order['discount'] = $total['discount'];
		$order['surplus'] = $total['surplus'];
		$order['tax'] = $total['tax'];
		$discount_amout = compute_discount_amount($_SESSION['cart_value']);
		$temp_amout = $order['goods_amount'] - $discount_amout;

		if ($temp_amout <= 0) {
			$order['bonus_id'] = 0;
		}

		if (!empty($order['shipping_id'])) {
			if (count($_POST['shipping']) == 1) {
				$shipping = shipping_info($order['shipping_id']);
			}

			$order['shipping_name'] = addslashes($shipping['shipping_name']);
		}

		$order['shipping_fee'] = $total['shipping_fee'];
		$order['insure_fee'] = $total['shipping_insure'];

		if (0 < $order['pay_id']) {
			$payment = payment_info($order['pay_id']);
			$order['pay_name'] = addslashes($payment['pay_name']);
		}

		$order['pay_fee'] = $total['pay_fee'];
		$order['cod_fee'] = $total['cod_fee'];

		if (0 < $order['pack_id']) {
			$pack = pack_info($order['pack_id']);
			$order['pack_name'] = addslashes($pack['pack_name']);
		}

		$order['pack_fee'] = $total['pack_fee'];

		if (0 < $order['card_id']) {
			$card = card_info($order['card_id']);
			$order['card_name'] = addslashes($card['card_name']);
		}

		$order['card_fee'] = $total['card_fee'];
		$order['order_amount'] = number_format($total['amount'], 2, '.', '');
		if (isset($_SESSION['direct_shopping']) && !empty($_SESSION['direct_shopping'])) {
			$where_flow = '&direct_shopping=' . $_SESSION['direct_shopping'];
		}

		if (($payment['pay_code'] == 'balance') && (0 < $order['order_amount'])) {
			if (0 < $order['surplus']) {
				$order['order_amount'] = $order['order_amount'] + $order['surplus'];
				$order['surplus'] = 0;
			}

			if (($user_info['user_money'] + $user_info['credit_line']) < $order['order_amount']) {
				show_message(l('balance_not_enough'), l('back_up_page'), u('flow/index/index') . $where_flow);
			}
			else if ($_SESSION['flow_type'] == CART_PRESALE_GOODS) {
				$order['surplus'] = $order['order_amount'];
				$order['pay_status'] = PS_PAYED_PART;
				$order['order_status'] = OS_CONFIRMED;
				$order['order_amount'] = ($order['goods_amount'] + $order['shipping_fee'] + $order['insure_fee'] + $order['tax']) - $order['discount'] - $order['surplus'];
			}
			else {
				$order['surplus'] = $order['order_amount'];
				$order['order_amount'] = 0;
			}
		}

		if ($order['order_amount'] <= 0) {
			$order['order_status'] = OS_CONFIRMED;
			$order['confirm_time'] = gmtime();
			$order['pay_status'] = PS_PAYED;
			$order['pay_time'] = gmtime();
			$order['order_amount'] = 0;
		}

		$order['integral_money'] = $total['integral_money'];
		$order['integral'] = $total['integral'];

		if ($order['extension_code'] == 'exchange_goods') {
			$order['integral_money'] = 0;
			$order['integral'] = $total['exchange_integral'];
		}

		$order['from_ad'] = !empty($_SESSION['from_ad']) ? $_SESSION['from_ad'] : '0';
		$order['referer'] = !empty($_SESSION['referer']) ? addslashes($_SESSION['referer']) : addslashes(l('self_site'));

		if ($flow_type != CART_GENERAL_GOODS) {
			$order['extension_code'] = $_SESSION['extension_code'];
			$order['extension_id'] = $_SESSION['extension_id'];
		}

		$affiliate = unserialize(c('shop.affiliate'));
		if (isset($affiliate['on']) && ($affiliate['on'] == 1) && ($affiliate['config']['separate_by'] == 1)) {
			$parent_id = get_affiliate();

			if ($user_id == $parent_id) {
				$parent_id = 0;
			}
		}
		else {
			if (isset($affiliate['on']) && ($affiliate['on'] == 1) && ($affiliate['config']['separate_by'] == 0)) {
				$parent_id = 0;
			}
			else {
				$parent_id = 0;
			}
		}

		$order['parent_id'] = $parent_id;
		$error_no = 0;

		do {
			$order['order_sn'] = get_order_sn();
			$new_order = $this->db->filter_field('order_info', $order);
			$new_order_id = $this->db->table('order_info')->data($new_order)->insert();
			$error_no = $GLOBALS['db']->errno();
			if ((0 < $error_no) && ($error_no != 1062)) {
				exit($GLOBALS['db']->errno());
			}
		} while ($error_no == 1062);

		$order['order_id'] = $new_order_id;

		if (file_exists(ROOT_PATH . 'source/apps/drp')) {
			$drp_affiliate = get_drp_affiliate_config();
			if (isset($drp_affiliate['on']) && ($drp_affiliate['on'] == 1)) {
				$sql = 'SELECT u.parent_id FROM {pre}users as u' . ' LEFT JOIN  {pre}drp_shop as ds ON u.parent_id = ds.user_id' . ' WHERE u.user_id = ' . $_SESSION['user_id'] . ' AND ds.audit = 1 AND ds.status = 1';
				$parent_id = $GLOBALS['db']->getOne($sql);

				if ($parent_id) {
					$is_distribution = 1;
				}
				else {
					$is_distribution = 0;
				}
			}

			$goodsIn = '';
			$cartValue = (isset($_SESSION['cart_value']) ? $_SESSION['cart_value'] : '');

			if (!empty($cartValue)) {
				$goodsIn = ' and ca.rec_id in(' . $cartValue . ')';
			}

			$sql = 'INSERT INTO ' . $this->ecs->table('order_goods') . '( ' . 'order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price, goods_price,' . 'goods_attr, is_real, extension_code, parent_id, is_gift, model_attr, goods_attr_id, ru_id, shopping_fee, warehouse_id, area_id, is_distribution, drp_money) ' . ' SELECT \'' . $new_order_id . '\', ca.goods_id, ca.goods_name, ca.goods_sn, ca.product_id, ca.goods_number, ca.market_price, ca.goods_price, ca.goods_attr, ' . 'ca.is_real, ca.extension_code, ca.parent_id, ca.is_gift, ca.model_attr, ca.goods_attr_id, ca.ru_id, ca.shopping_fee, ca.warehouse_id, ca.area_id,' . 'g.is_distribution*\'' . $is_distribution . '\' as is_distribution, ' . 'g.dis_commission*g.is_distribution*ca.goods_price*ca.goods_number/100*\'' . $is_distribution . '\' as drp_money' . ' FROM ' . $this->ecs->table('cart') . ' ca' . ' LEFT JOIN  {pre}goods as g ON ca.goods_id=g.goods_id' . ' WHERE ca.' . $this->sess_id . ' AND ca.rec_type = \'' . $flow_type . '\'' . $goodsIn;
			$this->db->query($sql);
		}
		else {
			$goodsIn = '';
			$cartValue = (isset($_SESSION['cart_value']) ? $_SESSION['cart_value'] : '');

			if (!empty($cartValue)) {
				$goodsIn = ' and rec_id in(' . $cartValue . ')';
			}

			$sql = 'INSERT INTO ' . $this->ecs->table('order_goods') . '( ' . 'order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price, ' . 'goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, model_attr, goods_attr_id, ru_id, shopping_fee, warehouse_id, area_id) ' . ' SELECT \'' . $new_order_id . '\', goods_id, goods_name, goods_sn, product_id, goods_number, market_price, ' . 'goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, model_attr, goods_attr_id, ru_id, shopping_fee, warehouse_id, area_id' . ' FROM ' . $this->ecs->table('cart') . ' WHERE ' . $this->sess_id . ' AND rec_type = \'' . $flow_type . '\'' . $goodsIn;
			$this->db->query($sql);
		}

		if (0 < $order['cou_id']) {
			$this->use_coupons($order['cou_id'], $order['order_id']);
		}

		if ($order['extension_code'] == 'auction') {
			$sql = 'UPDATE {pre}goods_activity SET is_finished=\'2\' WHERE act_id=' . $order['extension_id'];
			$this->db->query($sql);
		}

		if ((0 < $order['user_id']) && (0 < $order['surplus'])) {
			log_account_change($order['user_id'], $order['surplus'] * -1, 0, 0, 0, '订单:' . $order['order_sn'], $order['order_sn']);
		}

		if ((0 < $order['user_id']) && (0 < $order['integral'])) {
			log_account_change($order['user_id'], 0, 0, 0, $order['integral'] * -1, sprintf(l('pay_order'), $order['order_sn']));
		}

		if ((0 < $order['bonus_id']) && (0 < $temp_amout)) {
			use_bonus($order['bonus_id'], $new_order_id);
		}

		if ((c('shop.use_storage') == '1') && (c('shop.stock_dec_time') == SDT_PLACE)) {
			change_order_goods_storage($order['order_id'], true, SDT_PLACE);
		}

		if (count($cart_goods) <= 1) {
			if (1 <= $cart_goods[0]['ru_id']) {
				$sql = 'SELECT seller_email FROM ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' WHERE ru_id = \'' . $cart_goods[0]['ru_id'] . '\'';
				$service_email = $GLOBALS['db']->getOne($sql);
			}
			else {
				$service_email = c('shop.service_email');
			}
		}
		else {
			$service_email = c('shop.service_email');
		}

		$msg = ($order['pay_status'] == PS_UNPAYED ? l('order_placed_sms') : l('order_placed_sms') . '[' . l('sms_paid') . ']');

		if ($order['order_amount'] <= 0) {
			$sql = 'SELECT goods_id, goods_name, goods_number AS num FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE is_real = 0 AND extension_code = \'virtual_card\'' . ' AND ' . $this->sess_id . ' AND rec_type = \'' . $flow_type . '\'';
			$res = $GLOBALS['db']->getAll($sql);
			$virtual_goods = array();

			foreach ($res as $row) {
				$virtual_goods['virtual_card'][] = array('goods_id' => $row['goods_id'], 'goods_name' => $row['goods_name'], 'num' => $row['num']);
			}

			if ($virtual_goods && ($flow_type != CART_GROUP_BUY_GOODS)) {
				if (virtual_goods_ship($virtual_goods, $msg, $order['order_sn'], true)) {
					$sql = 'SELECT COUNT(*)' . ' FROM ' . $this->ecs->table('order_goods') . ' WHERE order_id = \'' . $order['order_id'] . '\' ' . ' AND is_real = 1';

					if ($this->db->getOne($sql) <= 0) {
						update_order($order['order_id'], array('shipping_status' => SS_SHIPPED, 'shipping_time' => gmtime()));

						if (0 < $order['user_id']) {
							$user = user_info($order['user_id']);
							$integral = integral_to_give($order);
							log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($GLOBALS['LANG']['order_gift_integral'], $order['order_sn']));
							send_order_bonus($order['order_id']);
						}
					}
				}
			}
		}

		clear_cart($flow_type, $_SESSION['cart_value']);
		clear_all_files();
		$order['log_id'] = insert_pay_log($new_order_id, $order['order_amount'], PAY_ORDER);
		$payment = payment_info($order['pay_id']);
		$order['pay_code'] = $payment['pay_code'];

		if (0 < $order['order_amount']) {
			include_once 'plugins/payment/' . $payment['pay_code'] . '.php';
			$pay_obj = new $payment['pay_code']();
			$pay_online = $pay_obj->get_code($order, unserialize_config($payment['pay_config']));
			$order['pay_desc'] = $payment['pay_desc'];
			$this->assign('pay_online', $pay_online);
		}

		if (!empty($order['shipping_name'])) {
			$order['shipping_name'] = trim(stripcslashes($order['shipping_name']));
		}

		$this->assign('order', $order);
		$this->assign('total', $total);
		$this->assign('goods_list', $cart_goods);
		$this->assign('order_submit_back', sprintf($GLOBALS['LANG']['order_submit_back'], $GLOBALS['LANG']['back_home'], $GLOBALS['LANG']['goto_user_center']));
		user_uc_call('add_feed', array($order['order_id'], BUY_GOODS));
		unset($_SESSION['flow_consignee']);
		unset($_SESSION['cart_value']);
		unset($_SESSION['flow_order']);
		unset($_SESSION['direct_shopping']);
		$order_id = $order['order_id'];
		$row = get_main_order_info($order_id);
		$order_info = get_main_order_info($order_id, 1);
		$ru_id = explode(',', $order_info['all_ruId']['ru_id']);
		$ru_number = count($ru_id);

		if (1 < $ru_number) {
			get_insert_order_goods_single($order_info, $row, $order_id, $postscript, $ru_number);
		}

		$sql = 'select count(order_id) from ' . $this->ecs->table('order_info') . ' where main_order_id = ' . $order['order_id'];
		$child_order = $this->db->getOne($sql);

		if (1 < $child_order) {
			$child_order_info = get_child_order_info($order['order_id']);
			$this->assign('child_order_info', $child_order_info);
		}

		$this->assign('pay_type', $pay_type);
		$this->assign('child_order', $child_order);

		if (count($ru_id) == 1) {
			$sellerId = $ru_id[0];

			if ($sellerId == 0) {
				$sms_shop_mobile = c('shop.sms_shop_mobile');
			}
			else {
				$sql = 'SELECT mobile FROM ' . $this->ecs->table('seller_shopinfo') . ' WHERE ru_id = \'' . $sellerId . '\'';
				$sms_shop_mobile = $this->db->getOne($sql);
			}

			if ((c('shop.sms_order_placed') == '1') && ($sms_shop_mobile != '')) {
				$msg = ($order['pay_status'] == PS_UNPAYED ? l('order_placed_sms') : l('order_placed_sms') . '[' . l('sms_paid') . ']');
				$sms_type = (c('shop.sms_type') ? 'Alidayu' : 'Ihuyi');
				send_sms($sms_shop_mobile, sprintf($msg, $order['consignee'], $order['mobile']), $sms_type);
			}

			if (c('shop.send_service_email') && ($service_email != '')) {
				$tpl = get_mail_template('remind_of_new_order');
				$this->assign('order', $order);
				$this->assign('goods_list', $cart_goods);
				$this->assign('shop_name', c('shop.shop_name'));
				$this->assign('send_date', date(c('shop.time_format')));
				$tpl['template_content'] = str_replace('{foreach from=$', '{foreach $', $tpl['template_content']);
				$tpl['template_content'] = str_replace(' item=', ' as $', $tpl['template_content']);
				$tpl['template_content'] = str_replace(' neq ', ' != ', $tpl['template_content']);
				$tpl['template_content'] = str_replace(' eq ', ' == ', $tpl['template_content']);
				$content = $this->fetch($tpl['template_content']);
				send_mail(c('shop.shop_name'), $service_email, $tpl['template_subject'], $content, $tpl['is_html']);
			}
		}

		if (class_exists('\\apps\\wechat\\controllers\\IndexController')) {
			$pushData = array(
				'orderID'         => array('value' => $order['order_sn']),
				'orderMoneySum'   => array('value' => $order['order_amount']),
				'backupFieldName' => array('value' => ''),
				'remark'          => array('value' => '感谢您的光临')
				);
			$url = __HOST__ . u('user/order/detail', array('order_id' => $order_id));
			pushtemplate('TM00016', $pushData, $url);
		}

		$this->assign('page_title', l('order_success'));
		$this->display('done');
	}

	public function actionShippingfee()
	{
		if (IS_AJAX) {
			$result = array('error' => 0, 'massage' => '', 'content' => '', 'need_insure' => 0, 'payment' => 1);
			$flow_type = (isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS);
			$shipping_type = (isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0);
			$tmp_shipping_id = (isset($_POST['shipping_id']) ? intval($_POST['shipping_id']) : 0);
			$ru_id = (isset($_REQUEST['ru_id']) ? intval($_REQUEST['ru_id']) : 0);
			$consignee = get_consignee($_SESSION['user_id']);
			$cart_goods = cart_goods($flow_type, $_SESSION['cart_value']);
			if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
				if (empty($cart_goods)) {
					$result['error'] = 1;
				}
				else if (!check_consignee_info($consignee, $flow_type)) {
					$result['error'] = 2;
				}
			}
			else {
				$this->assign('config', c('shop'));
				$order = flow_order_info();
				$_SESSION['flow_order'] = $order;

				if ($shipping_type == 1) {
					if (is_array($_SESSION['shipping_type_ru_id'])) {
						$_SESSION['shipping_type_ru_id'][$ru_id] = $ru_id;
					}
				}
				else if (isset($_SESSION['shipping_type_ru_id'][$ru_id])) {
					unset($_SESSION['shipping_type_ru_id'][$ru_id]);
				}

				$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
				$this->assign('cart_goods_number', $cart_goods_number);
				$consignee['province_name'] = get_goods_region_name($consignee['province']);
				$consignee['city_name'] = get_goods_region_name($consignee['city']);
				$consignee['district_name'] = get_goods_region_name($consignee['district']);
				$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'];
				$this->assign('consignee', $consignee);
				$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1);
				$this->assign('goods_list', cart_by_favourable($cart_goods_list));

				foreach ($cart_goods_list as $key => $val) {
					if ((0 < $tmp_shipping_id) && ($val['ru_id'] == $ru_id)) {
						$cart_goods_list[$key]['tmp_shipping_id'] = $tmp_shipping_id;
					}
				}

				$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
				$this->assign('total', $total);

				if ($flow_type == CART_GROUP_BUY_GOODS) {
					$this->assign('is_group_buy', 1);
				}

				$result['amount'] = $total['amount_formated'];
				$result['content'] = $this->fetch('order_total', true, true);
			}

			exit(json_encode($result));
		}
	}

	public function actionSelectPayment()
	{
		if (IS_AJAX) {
			$result = array('error' => 0, 'massage' => '', 'content' => '', 'need_insure' => 0, 'payment' => 1);
			$flow_type = (isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS);
			$tmp_shipping_id_arr = i('shipping_id');
			$consignee = get_consignee($_SESSION['user_id']);
			$cart_goods = cart_goods($flow_type, $_SESSION['cart_value']);
			if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
				if (empty($cart_goods)) {
					$result['error'] = 1;
				}
				else if (!check_consignee_info($consignee, $flow_type)) {
					$result['error'] = 2;
				}
			}
			else {
				$this->assign('config', c('shop'));
				$order = flow_order_info();
				$order['pay_id'] = intval($_REQUEST['payment']);
				$payment_info = payment_info($order['pay_id']);
				$result['pay_code'] = $payment_info['pay_code'];
				$result['pay_name'] = $payment_info['pay_name'];
				$result['pay_fee'] = $payment_info['pay_fee'];
				$result['format_pay_fee'] = strpos($payment_info['pay_fee'], '%') !== false ? $payment_info['pay_fee'] : price_format($payment_info['pay_fee'], false);
				$result['pay_id'] = $payment_info['pay_id'];
				$_SESSION['flow_order'] = $order;
				$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
				$this->assign('cart_goods_number', $cart_goods_number);
				$consignee['province_name'] = get_goods_region_name($consignee['province']);
				$consignee['city_name'] = get_goods_region_name($consignee['city']);
				$consignee['district_name'] = get_goods_region_name($consignee['district']);
				$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'];
				$this->assign('consignee', $consignee);
				$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1);

				foreach ($cart_goods_list as $key => $val) {
					foreach ($tmp_shipping_id_arr as $k => $v) {
						if ((0 < $v[1]) && ($val['ru_id'] == $v[0])) {
							$cart_goods_list[$key]['tmp_shipping_id'] = $v[1];
						}
					}
				}

				$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
				$this->assign('total', $total);

				if ($flow_type == CART_GROUP_BUY_GOODS) {
					$this->assign('is_group_buy', 1);
				}

				$result['amount'] = $total['amount_formated'];
				$result['content'] = $this->fetch('order_total', true, true);
			}

			exit(json_encode($result));
		}
	}

	public function actionSelectPack()
	{
		if (IS_AJAX) {
			$result = array('error' => '', 'content' => '', 'need_insure' => 0);
			$flow_type = (isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS);
			$consignee = get_consignee($_SESSION['user_id']);
			$cart_goods = cart_goods($flow_type, $_SESSION['cart_value']);
			if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
				$result['error'] = l('no_goods_in_cart');
			}
			else {
				$order = flow_order_info();
				$order['pack_id'] = intval($_REQUEST['pack']);
				$_SESSION['flow_order'] = $order;
				$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
				$this->assign('cart_goods_number', $cart_goods_number);
				$consignee['province_name'] = get_goods_region_name($consignee['province']);
				$consignee['city_name'] = get_goods_region_name($consignee['city']);
				$consignee['district_name'] = get_goods_region_name($consignee['district']);
				$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'];
				$this->assign('consignee', $consignee);
				$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1);
				$this->assign('goods_list', $cart_goods_list);
				$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
				$this->assign('total', $total);
				$this->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);
				$this->assign('total_bonus', price_format(get_total_bonus(), false));

				if ($flow_type == CART_GROUP_BUY_GOODS) {
					$this->assign('is_group_buy', 1);
				}

				$result['pack_id'] = $order['pack_id'];
				$result['amount'] = $total['amount_formated'];
				$result['content'] = $this->fetch('order_total', true, true);
			}

			exit(json_encode($result));
		}
	}

	public function actionChangeBonus()
	{
		$result = array('error' => '', 'content' => '');
		$flow_type = (isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS);
		$consignee = get_consignee($_SESSION['user_id']);
		$cart_goods = cart_goods($flow_type, $_SESSION['cart_value']);
		if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
			$result['error'] = $GLOBALS['_LANG']['no_goods_in_cart'];
		}
		else {
			$this->assign('config', c('shop'));
			$order = flow_order_info();
			$bonus = bonus_info(intval($_GET['bonus']));
			if ((!empty($bonus) && ($bonus['user_id'] == $_SESSION['user_id'])) || ($_GET['bonus'] == 0)) {
				$order['bonus_id'] = intval($_GET['bonus']);
			}
			else {
				$order['bonus_id'] = 0;
				$result['error'] = $GLOBALS['_LANG']['invalid_bonus'];
			}

			$consignee['province_name'] = get_goods_region_name($consignee['province']);
			$consignee['city_name'] = get_goods_region_name($consignee['city']);
			$consignee['district_name'] = get_goods_region_name($consignee['district']);
			$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'];
			$this->assign('consignee', $consignee);
			$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1);
			$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
			$this->assign('total', $total);
			$this->assign('order', $order);

			if ($flow_type == CART_GROUP_BUY_GOODS) {
				$this->assign('is_group_buy', 1);
			}

			$result['bonus_id'] = $order['bonus_id'];
			$result['amount'] = $total['amount_formated'];
			$result['content'] = $this->fetch('order_total', true, true);
		}

		exit(json_encode($result));
	}

	public function actionChangeCount()
	{
		$id = i('cou_id');
		$order = flow_order_info();
		$result = array('error' => '', 'content' => '');
		$flow_type = (isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS);
		$consignee = get_consignee($_SESSION['user_id']);
		$cart_goods = cart_goods($flow_type, $_SESSION['cart_value']);
		if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
			$result['error'] = $_LANG['no_goods_in_cart'];
		}
		else {
			$this->assign('config', $_CFG);
			$order = flow_order_info();
			$_SESSION['flow_order'] = null;
			$coupons_info = $this->get_coupons($id);
			if (!empty($coupons_info) && ($coupons_info['user_id'] == $_SESSION['user_id']) && ($id != 0)) {
				$order['uc_id'] = $id;
			}
			else {
				$order['uc_id'] = 0;
				$result['error'] = '您选择的优惠券不存在!';
				exit();
			}

			$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
			$this->assign('cart_goods_number', $cart_goods_number);
			$consignee['province_name'] = get_goods_region_name($consignee['province']);
			$consignee['city_name'] = get_goods_region_name($consignee['city']);
			$consignee['district_name'] = get_goods_region_name($consignee['district']);
			$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'];
			$this->assign('consignee', $consignee);
			$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1);
			$this->assign('goods_list', $cart_goods_list);
			$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
			$this->assign('total', $total);

			if ($flow_type == CART_GROUP_BUY_GOODS) {
				$this->assign('is_group_buy', 1);
			}
			else if ($flow_type == CART_EXCHANGE_GOODS) {
				$this->assign('is_exchange_goods', 1);
			}

			$result['content'] = $this->fetch('order_total', true, true);
		}

		exit(json_encode($result));
	}

	public function actionChangeIntegral()
	{
		$points = floatval($_GET['points']);
		$user_info = user_info($_SESSION['user_id']);
		$order = flow_order_info();
		$flow_points = flow_available_points($_SESSION['cart_value']);
		$user_points = $user_info['pay_points'];
		$tmp_shipping_id_arr = i('shipping_id');

		if ($user_points < $points) {
			$result['error'] = l('integral_not_enough');
		}
		else if ($flow_points < $points) {
			$result['error'] = sprintf($GLOBALS['_LANG']['integral_too_much'], $flow_points);
		}
		else {
			$flow_type = (isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS);
			$order['integral'] = $points;
			$consignee = get_consignee($_SESSION['user_id']);
			$cart_goods = cart_goods($flow_type, $_SESSION['cart_value']);
			if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
				$result['error'] = $GLOBALS['_LANG']['no_goods_in_cart'];
			}
			else {
				$cart_goods_number = get_buy_cart_goods_number($flow_type, $_SESSION['cart_value']);
				$this->assign('cart_goods_number', $cart_goods_number);
				$consignee['province_name'] = get_goods_region_name($consignee['province']);
				$consignee['city_name'] = get_goods_region_name($consignee['city']);
				$consignee['district_name'] = get_goods_region_name($consignee['district']);
				$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'];
				$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1);

				foreach ($cart_goods_list as $key => $val) {
					foreach ($tmp_shipping_id_arr as $k => $v) {
						if ((0 < $v[1]) && ($val['ru_id'] == $v[0])) {
							$cart_goods_list[$key]['tmp_shipping_id'] = $v[1];
						}
					}
				}

				$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
				$this->assign('total', $total);
				$this->assign('config', c('shop'));

				if ($flow_type == CART_GROUP_BUY_GOODS) {
					$this->assign('is_group_buy', 1);
				}

				$result['integral'] = $order['integral'];
				$result['amount'] = $total['amount_formated'];
				$result['content'] = $this->fetch('order_total', true, true);
				$result['error'] = '';
			}
		}

		exit(json_encode($result));
	}

	public function actionCheckIntegral()
	{
		if (IS_AJAX) {
			$points = floatval($_GET['integral']);
			$user_info = user_info($_SESSION['user_id']);
			$flow_points = flow_available_points($_SESSION['cart_value']);
			$user_points = $user_info['pay_points'];

			if ($user_points < $points) {
				exit($GLOBALS['_LANG']['integral_not_enough']);
			}

			if ($flow_points < $points) {
				exit(sprintf($GLOBALS['_LANG']['integral_too_much'], $flow_points));
			}

			exit();
		}
	}

	public function actionSelectPicksite()
	{
		$result = array('error' => 0, 'err_msg' => '', 'content' => '');
		$ru_id = i('request.ru_id', 0, 'intval');

		if (isset($_REQUEST['picksite_id'])) {
			$picksite_id = i('request.picksite_id', 0, 'intval');

			if (is_array($_SESSION['flow_consignee']['point_id'])) {
				$_SESSION['flow_consignee']['point_id'][$ru_id] = $picksite_id;
			}
		}
		else {
			if (isset($_REQUEST['shipping_date']) && isset($_REQUEST['time_range'])) {
				$shipping_date = i('request.shipping_date');
				$time_range = i('request.time_range');
				$_SESSION['flow_consignee']['shipping_dateStr'] = $shipping_date . $time_range;
			}
		}

		$flow_type = (isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS);
		$consignee = get_consignee($_SESSION['user_id']);
		$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1);
		if (empty($cart_goods_list) || !check_consignee_info($consignee, $flow_type)) {
			if (empty($cart_goods)) {
				$result['error'] = 1;
				$result['err_msg'] = l('no_goods_in_cart');
			}
			else if (!check_consignee_info($consignee, $flow_type)) {
				$result['error'] = 2;
				$result['err_msg'] = l('au_buy_after_login');
			}
		}

		exit(json_encode($result));
	}

	public function actionChangeNeedinv()
	{
		$result = array('error' => '', 'content' => '', 'amount' => '');
		$_GET['inv_type'] = !empty($_GET['inv_type']) ? json_str_iconv(urldecode($_GET['inv_type'])) : '';
		$_GET['invPayee'] = !empty($_GET['invPayee']) ? json_str_iconv(urldecode($_GET['invPayee'])) : '';
		$_GET['inv_content'] = !empty($_GET['inv_content']) ? json_str_iconv(urldecode($_GET['inv_content'])) : '';
		$flow_type = (isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS);
		$consignee = get_consignee($_SESSION['user_id']);
		$cart_goods = cart_goods($flow_type, $_SESSION['cart_value']);
		if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type)) {
			$result['error'] = $GLOBALS['_LANG']['no_goods_in_cart'];
			exit(json_encode($result));
		}
		else {
			$this->assign('config', c('shop'));
			$order = flow_order_info();
			if (isset($_GET['need_inv']) && (intval($_GET['need_inv']) == 1)) {
				$order['need_inv'] = 1;
				$order['inv_type'] = trim(stripslashes($_GET['inv_type']));
				$order['inv_payee'] = trim(stripslashes($_GET['inv_payee']));
				$order['inv_content'] = trim(stripslashes($_GET['inv_content']));
			}
			else {
				$order['need_inv'] = 0;
				$order['inv_type'] = '';
				$order['inv_payee'] = '';
				$order['inv_content'] = '';
			}

			$consignee['province_name'] = get_goods_region_name($consignee['province']);
			$consignee['city_name'] = get_goods_region_name($consignee['city']);
			$consignee['district_name'] = get_goods_region_name($consignee['district']);
			$consignee['consignee_address'] = $consignee['province_name'] . $consignee['city_name'] . $consignee['district_name'] . $consignee['address'];
			$this->assign('consignee', $consignee);
			$cart_goods_list = cart_goods($flow_type, $_SESSION['cart_value'], 1);
			$total = order_fee($order, $cart_goods, $consignee, 0, $_SESSION['cart_value'], 0, $cart_goods_list);
			$this->assign('total', $total);

			if ($flow_type == CART_GROUP_BUY_GOODS) {
				$this->assign('is_group_buy', 1);
			}

			$result['amount'] = $total['amount_formated'];
			$result['content'] = $this->fetch('order_total', true, true);
			exit(json_encode($result));
		}
	}

	public function actionAddressList()
	{
		if (IS_AJAX) {
			$id = i('address_id');
			drop_consignee($id);
			unset($_SESSION['flow_consignee']);
			exit();
		}

		$user_id = $_SESSION['user_id'];

		if (0 < $_SESSION['user_id']) {
			$consignee_list = get_consignee_list($_SESSION['user_id']);
		}
		else if (isset($_SESSION['flow_consignee'])) {
			$consignee_list = array($_SESSION['flow_consignee']);
		}
		else {
			$consignee_list[] = array('country' => c('shop.shop_country'));
		}

		$this->assign('name_of_region', array(c('shop.name_of_region_1'), c('shop.name_of_region_2'), c('shop.name_of_region_3'), c('shop.name_of_region_4')));

		if ($consignee_list) {
			foreach ($consignee_list as $k => $v) {
				$address = '';

				if ($v['province']) {
					$res = get_region_name($v['province']);
					$address .= $res['region_name'];
				}

				if ($v['city']) {
					$ress = get_region_name($v['city']);
					$address .= $ress['region_name'];
				}

				if ($v['district']) {
					$resss = get_region_name($v['district']);
					$address .= $resss['region_name'];
				}

				$consignee_list[$k]['address'] = $address . ' ' . $v['address'];
				$consignee_list[$k]['url'] = u('user/edit_address', array('id' => $v['address_id']));
			}
		}

		$default_id = $this->db->getOne('SELECT address_id FROM {pre}users WHERE user_id=\'' . $user_id . '\'');
		$address_id = $_SESSION['flow_consignee']['address_id'];
		$this->assign('defulte_id', $default_id);
		$this->assign('address_id', $address_id);
		$this->assign('consignee_list', $consignee_list);
		$this->assign('page_title', l('receiving_address'));
		$this->display('flow_get_consignee');
	}

	public function actionAddAddress()
	{
		if (IS_POST) {
			$consignee = array('address_id' => i('address_id'), 'consignee' => i('consignee'), 'country' => 1, 'province' => i('province_region_id'), 'city' => i('city_region_id'), 'district' => i('district_region_id'), 'email' => i('email'), 'address' => i('address'), 'zipcode' => i('zipcode'), 'tel' => i('tel'), 'mobile' => i('mobile'), 'sign_building' => i('sign_building'), 'best_time' => i('best_time'), 'user_id' => $_SESSION['user_id']);

			if (empty($consignee['consignee'])) {
				show_message(l('msg_receiving_notnull'));
			}

			if (empty($consignee['mobile'])) {
				show_message(l('msg_contact_way_notnull'));
			}

			if (!preg_match('/^1[3|5|8|7|4]\\d{9}$/', $consignee['mobile'])) {
				show_message(l('msg_mobile_format_error'));
			}

			if (empty($consignee['district'])) {
				show_message(l('msg_area_notnull'));
			}

			if (empty($consignee['address'])) {
				show_message(l('msg_address_notnull'));
			}

			$limit_address = $this->db->getOne('select count(address_id) from {pre}user_address where user_id = \'' . $consignee['user_id'] . '\'');

			if (5 < $limit_address) {
				show_message(l('msg_save_address'));
			}

			if (0 < $_SESSION['user_id']) {
				save_consignee($consignee, false);
			}

			$_SESSION['flow_consignee'] = stripslashes_deep($consignee);
			ecs_header('Location: ' . u('flow/index/index') . "\n");
			exit();
		}

		$this->assign('user_id', $_SESSION['user_id']);
		$this->assign('country_list', get_regions());
		$this->assign('shop_country', c('shop.shop_country'));
		$this->assign('shop_province_list', get_regions(1, c('shop.shop_country')));
		$this->assign('address_id', i('address_id'));
		$province_list = get_regions(1, c('shop.shop_country'));
		$this->assign('province_list', $province_list);
		$city_list = get_region_city_county($this->province_id);

		if ($city_list) {
			foreach ($city_list as $k => $v) {
				$city_list[$k]['district_list'] = get_region_city_county($v['region_id']);
			}
		}

		$this->assign('city_list', $city_list);
		$district_list = get_region_city_county($this->city_id);
		$this->assign('district_list', $district_list);
		$this->assign('page_title', l('add_address'));
		$this->display('flow_consignee');
	}

	public function actionEditAddress()
	{
		if (IS_POST) {
			$consignee = array('address_id' => i('address_id'), 'consignee' => i('consignee'), 'country' => 1, 'province' => i('province_region_id'), 'city' => i('city_region_id'), 'district' => i('district_region_id'), 'email' => i('email'), 'address' => i('address'), 'zipcode' => i('zipcode'), 'tel' => i('tel'), 'mobile' => i('mobile'), 'sign_building' => i('sign_building'), 'best_time' => i('best_time'), 'user_id' => $_SESSION['user_id']);

			if (empty($consignee['consignee'])) {
				show_message(l('msg_receiving_notnull'));
			}

			if (empty($consignee['mobile'])) {
				show_message(l('msg_contact_way_notnull'));
			}

			if (!preg_match('/^1[3|5|8|7|4]\\d{9}$/', $consignee['mobile'])) {
				show_message(l('msg_mobile_format_error'));
			}

			if (empty($consignee['address'])) {
				show_message(l('msg_address_notnull'));
			}

			$limit_address = $this->db->getOne('select count(address_id) from {pre}user_address where user_id = \'' . $consignee['user_id'] . '\'');

			if (5 < $limit_address) {
				show_message(l('msg_save_address'));
			}

			if (0 < $_SESSION['user_id']) {
				save_consignee($consignee, TRUE);
			}

			$_SESSION['flow_consignee'] = stripslashes_deep($consignee);
			ecs_header('Location: ' . u('flow/index/index') . "\n");
			exit();
		}

		$this->assign('user_id', $_SESSION['user_id']);
		$this->assign('country_list', get_regions());
		$this->assign('shop_country', c('shop.shop_country'));
		$this->assign('shop_province_list', get_regions(1, c('shop.shop_country')));
		$this->assign('address_id', i('address_id'));
		$province_list = get_regions(1, c('shop.shop_country'));
		$this->assign('province_list', $province_list);
		$city_list = get_region_city_county($this->province_id);

		if ($city_list) {
			foreach ($city_list as $k => $v) {
				$city_list[$k]['district_list'] = get_region_city_county($v['region_id']);
			}
		}

		if (i('address_id')) {
			$address_id = $_GET['address_id'];
			$consignee_list = $this->db->getRow('SELECT * FROM {pre}user_address WHERE user_id=\'' . $_SESSION['user_id'] . ']\' AND address_id=\'' . $address_id . '\'');

			if (empty($consignee_list)) {
				show_message(l('not_exist_address'));
			}

			$c = get_region_name($consignee_list['province']);
			$cc = get_region_name($consignee_list['city']);
			$ccc = get_region_name($consignee_list['district']);
			$consignee_list['province'] = $c['region_name'];
			$consignee_list['city'] = $cc['region_name'];
			$consignee_list['district'] = $ccc['region_name'];
			$consignee_list['province_id'] = $c['region_id'];
			$consignee_list['city_id'] = $cc['region_id'];
			$consignee_list['district_id'] = $ccc['region_id'];
			$city_list = get_region_city_county($c['region_id']);

			if ($city_list) {
				foreach ($city_list as $k => $v) {
					$city_list[$k]['district_list'] = get_region_city_county($v['region_id']);
				}
			}

			$this->assign('consignee_list', $consignee_list);
		}

		$this->assign('city_list', $city_list);
		$district_list = get_region_city_county($this->city_id);
		$this->assign('district_list', $district_list);
		$this->assign('page_title', l('edit_address'));
		$this->display('flow_consignee_edit');
	}

	public function actionShowRegionName()
	{
		if (IS_AJAX) {
			$data['province'] = get_region_name(i('province'));
			$data['city'] = get_region_name(i('city'));
			$data['district'] = get_region_name(i('district'));
			exit(json_encode($data));
		}
	}

	public function actionSetAddress()
	{
		if (IS_AJAX) {
			$user_id = session('user_id');
			$address_id = (isset($_REQUEST['address_id']) ? intval($_REQUEST['address_id']) : 0);
			$sql = 'SELECT * FROM {pre}user_address WHERE address_id = \'' . $address_id . '\' AND user_id = \'' . $user_id . '\'';
			$address = $this->db->getRow($sql);

			if (!empty($address)) {
				$_SESSION['flow_consignee'] = $address;
				echo json_encode(array('url' => u('index'), 'status' => 1));
			}
			else {
				echo json_encode(array('status' => 0));
			}
		}
	}

	public function actionAddPackageToCart()
	{
		if (IS_AJAX) {
			$_POST['package_info'] = stripslashes($_POST['package_info']);
			$result = array('error' => 0, 'message' => '', 'content' => '', 'package_id' => '');

			if (empty($_POST['package_info'])) {
				$result['error'] = 1;
				exit(json_encode($result));
			}

			$package = json_decode($_POST['package_info']);

			if (c('shop.one_step_buy') == '1') {
				clear_cart();
			}

			if (!is_numeric($package->number) || (intval($package->number) <= 0)) {
				$result['error'] = 1;
				$result['message'] = l('invalid_number');
			}
			else if (add_package_to_cart($package->package_id, $package->number, $package->warehouse_id, $package->area_id)) {
				if (2 < c('shop.cart_confirm')) {
					$result['message'] = '';
				}
				else {
					$result['message'] = c('shop.cart_confirm') == 1 ? l('addto_cart_success_1') : l('addto_cart_success_2');
				}

				$result['content'] = insert_cart_info();
				$result['one_step_buy'] = c('shop.one_step_buy');
			}
			else {
				$result['message'] = $GLOBALS['err']->last_message();
				$result['error'] = $GLOBALS['err']->error_no;
				$result['package_id'] = stripslashes($package->package_id);
			}

			$confirm_type = (isset($package->confirm_type) ? $package->confirm_type : 0);

			if (0 < $confirm_type) {
				$result['confirm_type'] = $confirm_type;
			}
			else {
				$cart_confirm = c('shop.cart_confirm');
				$result['confirm_type'] = !empty($cart_confirm) ? $cart_confirm : 2;
			}

			exit(json_encode($result));
		}
	}

	public function check_login()
	{
		$without = array('AddPackageToCart');
		if (!$_SESSION['user_id'] && !in_array(ACTION_NAME, $without)) {
			ecs_header('Location: ' . u('user/login/index'));
		}
	}

	public function get_coupons($uc_id)
	{
		$time = gmtime();
		return $GLOBALS['db']->getRow(' SELECT c.*,cu.* FROM ' . $GLOBALS['ecs']->table('coupons_user') . ' cu LEFT JOIN ' . $GLOBALS['ecs']->table('coupons') . ' c ON c.cou_id=cu.cou_id WHERE cu.cou_id=\'' . $uc_id . '\' AND cu.user_id=\'' . $_SESSION['user_id'] . '\' AND c.cou_end_time>' . $time . ' ORDER BY  cu.uc_id DESC limit 1 ');
	}

	public function get_user_coupons_list($user_id = '', $is_use = false, $total = false, $cart_goods = false, $user = true)
	{
		$time = gmtime();
		if ($is_use && $total && $cart_goods) {
			foreach ($cart_goods as $k => $v) {
				$res[$v['ru_id']][] = $v;
			}

			foreach ($res as $k => $v) {
				foreach ($v as $m => $n) {
					$store_total[$k] += $n['goods_price'] * $n['goods_number'];
				}
			}

			foreach ($cart_goods as $k => $v) {
				foreach ($store_total as $m => $n) {
					$where = ' WHERE cu.is_use=0 AND c.cou_end_time > ' . $time . ' AND ' . $time . '>c.cou_start_time AND ' . $n . ' >= c.cou_man AND cu.user_id =\'' . $user_id . "'\r\n                        AND (c.cou_goods =0 OR FIND_IN_SET('" . $v['goods_id'] . '\',c.cou_goods)) AND c.ru_id=\'' . $v['ru_id'] . '\'';
					$sql = ' SELECT c.*,cu.*,o.order_sn,o.add_time FROM ' . $GLOBALS['ecs']->table('coupons_user') . ' cu LEFT JOIN ' . $GLOBALS['ecs']->table('coupons') . ' c ON c.cou_id=cu.cou_id LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' o ON cu.order_id=o.order_id ' . $where . ' ';
					$arrr[] = $GLOBALS['db']->getAll($sql);
				}
			}

			foreach ($arrr as $k => $v) {
				foreach ($v as $m => $n) {
					$arr[$n['uc_id']] = $n;
				}
			}

			return $arr;
		}
		else {
			if (!empty($user_id) && $user) {
				$where = ' WHERE cu.user_id IN(' . $user_id . ')';
			}
			else if (!empty($user_id)) {
				$where = ' WHERE cu.user_id IN(' . $user_id . ') GROUP BY c.cou_id';
			}

			$res = $GLOBALS['db']->getAll(' SELECT c.*,cu.*,o.order_sn,o.add_time FROM ' . $GLOBALS['ecs']->table('coupons_user') . ' cu LEFT JOIN ' . $GLOBALS['ecs']->table('coupons') . ' c ON c.cou_id=cu.cou_id LEFT JOIN ' . $GLOBALS['ecs']->table('order_info') . ' o ON cu.order_id=o.order_id ' . $where . ' ');
			return $res;
		}
	}

	public function use_coupons($cou_id, $order_id)
	{
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('coupons_user') . ' SET order_id = \'' . $order_id . '\', is_use_time = \'' . gmtime() . '\', is_use =1 ' . 'WHERE cou_id = \'' . $cou_id . '\'';
		return $GLOBALS['db']->query($sql);
	}
}

?>