<?php
//dezend by  QQ:2172298892
class post_express
{
	/**
     * 配置信息
     */
	public $configure;

	public function post_express($cfg = array())
	{
		foreach ($cfg as $key => $val) {
			$this->configure[$val['name']] = $val['value'];
		}
	}

	public function calculate($goods_weight, $goods_amount, $goods_number)
	{
		if ((0 < $this->configure['free_money']) && ($this->configure['free_money'] <= $goods_amount)) {
			return 0;
		}
		else {
			$fee = $this->configure['base_fee'];
			$this->configure['fee_compute_mode'] = !empty($this->configure['fee_compute_mode']) ? $this->configure['fee_compute_mode'] : 'by_weight';

			if ($this->configure['fee_compute_mode'] == 'by_number') {
				$fee = $goods_number * $this->configure['item_fee'];
			}
			else if (5 < $goods_weight) {
				$fee += 8 * $this->configure['step_fee'];
				$fee += ceil(($goods_weight - 5) / 0.5) * $this->configure['step_fee1'];
			}
			else if (1 < $goods_weight) {
				$fee += ceil(($goods_weight - 1) / 0.5) * $this->configure['step_fee'];
			}

			return $fee;
		}
	}

	public function query($invoice_sn)
	{
		$str = '<a class="btn-submit" href="http://wap.guoguo-app.com/wuliuDetail.htm?mailNo=' . $invoice_sn . '">订单跟踪</a>';
		return $str;
	}

	public function calculate_insure($total_price, $insure_rate)
	{
		$total_price = ceil($total_price);
		$price = $total_price * $insure_rate;

		if ($price < 1) {
			$price = 1;
		}

		return ceil($price);
	}
}

defined('BASE_PATH') || exit('No direct script access allowed');

?>
