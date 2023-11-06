<?php

defined('ABSPATH') || exit;

$order = wc_get_order($order_id); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

if (!$order) {
	return;
}

$order_items = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
$show_purchase_note = $order->has_status(apply_filters('woocommerce_purchase_note_order_statuses', array('completed', 'processing')));
$show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
$downloads = $order->get_downloadable_items();
$show_downloads = $order->has_downloadable_item() && $order->is_download_permitted();

if ($show_downloads) {
	wc_get_template(
		'order/order-downloads.php',
		array(
			'downloads' => $downloads,
			'show_title' => true,
		)
	);
}
?>

<?php
do_action('woocommerce_order_details_before_order_table_items', $order);

foreach ($order_items as $item_id => $item) {
	$product = $item->get_product();

	wc_get_template(
		'order/order-details-item.php',
		array(
			'order' => $order,
			'item_id' => $item_id,
			'item' => $item,
			'show_purchase_note' => $show_purchase_note,
			'purchase_note' => $product ? $product->get_purchase_note() : '',
			'product' => $product,
		)
	);

	$Produtos .= "*" . $item->get_quantity() . "x " . $item->get_name() . "%0a%0a";
	$soma += $item->get_total();
}

do_action('woocommerce_order_details_after_order_table_items', $order);

foreach ($order->meta_data as $valor) {
	if ($valor->key == "_billing_numero") {
		$numero = $valor->value;
	}

	if ($valor->key == "billing_complemento") {
		$complemento = $valor->value;
	}
}

$dados .= "*ORÇAMENTO ENVIADO* %0a";
$dados .= "--------------------------------%0a";
$dados .= "*RESUMO DO ORÇAMENTO* %0a%0a";
$dados .= "*Cód:*" . $order->get_id() . "%0a";
$dados .= "*PRODUTOS* %0a%0a";
$dados .= $Produtos;
$dados .= "--------------------------------%0a";
$dados .= "* SUBTOTAL: * " . number_format($soma, 2, ',', '.') . "KZ %0a%0a";
$dados .= "--------------------------------%0a";
$dados .= "* DADOS DO CLIENTE * %0a%0a";
$dados .= "* Nome:* " . $order->get_billing_first_name() . " " . $order->get_billing_last_name() . "%0a%0a";
$dados .= "* Endereço: * " . $order->get_billing_address_1() . " , " . $numero . " %0a";
$dados .= "* Cidade: * " . $order->get_billing_city() . " %0a";
$dados .= "* Bairro: * " . $order->get_billing_address_2() . " %0a";
$dados .= "* Complemento: * " . $complemento . " %0a";
$dados .= "* Telefone/WhatsApp: * " . $order->get_billing_phone() . " %0a";

$telefone = WHATSAPP;

header("Location: https://api.whatsapp.com/send?phone=".$telefone."&text=".$dados);


?>