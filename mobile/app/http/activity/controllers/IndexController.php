<?php
//dezend by  QQ:2172298892
namespace http\activity\controllers;

class IndexController extends \http\base\controllers\FrontendController
{
	public function __construct()
	{
		parent::__construct();
		l(require LANG_PATH . c('shop.lang') . '/user.php');
	}

	public function actionIndex()
	{
		$user_rank_list = array();
		$user_rank_list[0] = l('not_user');
		$sql = 'SELECT rank_id, rank_name FROM ' . $this->ecs->table('user_rank');
		$res = $this->db->query($sql);

		foreach ($res as $row) {
			$user_rank_list[$row['rank_id']] = $row['rank_name'];
		}

		$sql = 'SELECT * FROM ' . $this->ecs->table('favourable_activity') . ' ORDER BY `sort_order` ASC,`end_time` DESC';
		$res = $this->db->query($sql);
		$list = array();

		foreach ($res as $row) {
			$row['activity_thumb'] = get_image_path($row['activity_thumb']);
			$row['start_time'] = local_date('Y-m-d H:i', $row['start_time']);
			$row['end_time'] = local_date('Y-m-d H:i', $row['end_time']);
			$row['url'] = build_uri('activity', array('actid' => $row['act_id']));
			$row['actType'] = $row['act_type'];

			switch ($row['act_type']) {
			case 0:
				$row['act_type'] = l('fat_goods');
				break;

			case 1:
				$row['act_type'] = l('fat_price');
				$row['act_type_ext'] .= l('unit_yuan');
				break;

			case 2:
				$row['act_type'] = l('fat_discount');
				$row['act_type_ext'] .= '%';
				break;
			}

			$list[$row['actType']]['activity_name'] = $row['act_type'];
			$list[$row['actType']]['activity_list'][] = $row;
		}

		$this->assign('list', $list);
		$this->assign('page_title', l('act_index'));
		$this->display('index');
	}

	public function actionDetail()
	{
		$id = i('id', 0, 'intval');

		if (empty($id)) {
			$this->redirect(u('site/index/index'));
		}

		$user_rank_list = array();
		$user_rank_list[0] = l('not_user');
		$sql = 'SELECT rank_id, rank_name FROM ' . $this->ecs->table('user_rank');
		$res = $this->db->query($sql);

		foreach ($res as $row) {
			$user_rank_list[$row['rank_id']] = $row['rank_name'];
		}

		$row = $this->db->getRow('SELECT * FROM ' . $this->ecs->table('favourable_activity') . ' WHERE act_id = ' . $id);
		$user_rank = explode(',', $row['user_rank']);
		$row['user_rank'] = array();

		foreach ($user_rank as $val) {
			if (isset($user_rank_list[$val])) {
				$row['user_rank'][] = $user_rank_list[$val];
			}
		}

		$row['start_time'] = local_date('Y-m-d H:i', $row['start_time']);
		$row['end_time'] = local_date('Y-m-d H:i', $row['end_time']);
		$row['activity_thumb'] = get_image_path($row['activity_thumb']);

		if ($row['userFav_type']) {
			$row['shop_name'] = l('shop_name');
		}
		else {
			$row['shop_name'] = get_shop_name($row['user_id'], 1);
			$row['shop_url'] = u('store/index/shop_info', array('id' => $row['user_id']));
		}

		$row['act_range_type'] = $row['act_range'];
		if (($row['act_range'] != FAR_ALL) && !empty($row['act_range_ext'])) {
			if ($row['act_range'] == FAR_CATEGORY) {
				$row['act_range'] = l('far_category');
				$sql = 'SELECT cat_id AS id, cat_name AS name FROM ' . $this->ecs->table('category') . ' WHERE cat_id ' . db_create_in($row['act_range_ext']);
			}
			else if ($row['act_range'] == FAR_BRAND) {
				$row['act_range'] = l('far_brand');
				$sql = 'SELECT brand_id AS id, brand_name AS name FROM ' . $this->ecs->table('brand') . ' WHERE brand_id ' . db_create_in($row['act_range_ext']);
			}
			else {
				$row['act_range'] = l('far_goods');
				$sql = 'SELECT goods_id AS id, goods_name AS name FROM ' . $this->ecs->table('goods') . ' WHERE goods_id ' . db_create_in($row['act_range_ext']);
			}

			$act_range_ext = $this->db->getAll($sql);
			$row['act_range_ext'] = $act_range_ext;
		}
		else {
			$row['act_range'] = l('far_all');
		}

		$row['actType'] = $row['act_type'];

		switch ($row['act_type']) {
		case 0:
			$row['act_type'] = l('fat_goods');
			$row['gift'] = unserialize($row['gift']);

			if (is_array($row['gift'])) {
				foreach ($row['gift'] as $k => $v) {
					$goods_thumb = $this->db->getOne('SELECT goods_thumb FROM ' . $this->ecs->table('goods') . ' WHERE goods_id = \'' . $v['id'] . '\'');
					$row['gift'][$k]['thumb'] = get_image_path($goods_thumb);
					$row['gift'][$k]['price'] = price_format($v['price'], false);
					$row['gift'][$k]['url'] = build_uri('goods', array('gid' => $v['id'], 'u' => $_SESSION['user_id']));
				}
			}

			break;

		case 1:
			$row['act_type'] = l('fat_price');
			$row['act_type_ext'] .= l('unit_yuan');
			$row['gift'] = array();
			break;

		case 2:
			$row['act_type'] = l('fat_discount');
			$row['act_type_ext'] .= '%';
			$row['gift'] = array();
			break;
		}

		$this->assign('info', $row);
		$this->assign('page_title', $row['act_name']);
		$this->display('detail');
	}

	public function actionGoodsList()
	{
		if (IS_AJAX) {
			$id = i('get.id', 0, 'intval');
			$page = i('post.page', 1, 'intval');
			$size = i('post.size', 4, 'intval');

			if (!empty($id)) {
				$province_id = (isset($_COOKIE['province']) ? $_COOKIE['province'] : 0);
				$area_info = get_area_info($province_id);
				$area_id = $area_info['region_id'];
				$where = 'regionId = \'' . $province_id . '\'';
				$date = array('parent_id');
				$region_id = get_table_date('region_warehouse', $where, $date, 2);
				if (isset($_COOKIE['region_id']) && !empty($_COOKIE['region_id'])) {
					$region_id = $_COOKIE['region_id'];
				}

				$row = $this->db->getRow('SELECT act_range, act_range_ext FROM ' . $this->ecs->table('favourable_activity') . ' WHERE act_id = ' . $id);
				$goods_list = array();
				if (($row['act_range'] != FAR_ALL) && !empty($row['act_range_ext'])) {
					if ($row['act_range'] == FAR_CATEGORY) {
						$cat_str = '';
						$cat_rows = explode(',', $row['act_range_ext']);

						if ($cat_rows) {
							foreach ($cat_rows as $v) {
								$cat_children = array_unique(array_merge(array($v), array_keys(cat_list($v, 0, false))));

								if ($cat_children) {
									$cat_str .= implode(',', $cat_children) . ',';
								}
							}
						}

						if ($cat_str) {
							$cat_str = substr($cat_str, 0, -1);
						}

						$goods_list = get_activity_goods($cat_str, '', '', $region_id, $area_id, $page, $size);
					}
					else if ($row['act_range'] == FAR_BRAND) {
						$goods_list = get_activity_goods('', $row['act_range_ext'], '', $region_id, $area_id, $page, $size);
					}
					else {
						$goods_list = get_activity_goods('', '', $row['act_range_ext'], $region_id, $area_id, $page, $size);
					}
				}
				else {
					$goods_list = get_activity_goods('', '', 1, $region_id, $area_id, $page, $size);
				}

				exit(json_encode(array('list' => $goods_list['list'], 'totalPage' => $goods_list['totalpage'])));
			}
		}
	}
}

?>
