<?php

class matrix_invoice 
{
	function getInvoiceItems($id) {
		
		$sql = "SELECT * FROM ".TB_PREFIX."invoice_items WHERE invoice_id = :id";
		$sth = dbQuery($sql, ':id', $id);
		
		$invoiceItems = null;
		
		for($i=0;$invoiceItem = $sth->fetch();$i++) {
		
			$invoiceItem['quantity'] = $invoiceItem['quantity'];
			$invoiceItem['unit_price'] = $invoiceItem['unit_price'];
			$invoiceItem['tax_amount'] = $invoiceItem['tax_amount'];
			$invoiceItem['gross_total'] = $invoiceItem['gross_total'];
			$invoiceItem['total'] = $invoiceItem['total'];
			
			$sql = "SELECT * FROM ".TB_PREFIX."products WHERE id = :id";
			$tth = dbQuery($sql, ':id', $invoiceItem['product_id']) or die(htmlspecialchars(end($dbh->errorInfo())));
			$invoiceItem['product'] = $tth->fetch();	
			
			$invoiceItems[$i] = $invoiceItem;
		}
		
		return $invoiceItems;
	}

	function insertInvoiceItem($invoice_id,$quantity,$product_id,$tax_id,$description="",$attr1="",$attr2="",$attr3=""  ) {
		
		/*strip attri of unneeded info - only need the last section - the attribute id*/
		
			$attr1 = explode("-",$attr1);
			$attr1 = $attr1[2];
			echo "Attr1: ".$attr1." ";

			$attr2 = explode("-",$attr2);
			$attr2 = $attr2[2];
			echo "Attr2 : ".$attr2." ";

			$attr3 = explode("-",$attr3);
			$attr3 = $attr3[2];
			echo "Attr3 : ".$attr3;
			echo "<br><br>";

		$tax = getTaxRate($tax_id);
		$product = getProduct($product_id);
		//print_r($product);
		$actual_tax = $tax['tax_percentage']  / 100 ;
		$total_invoice_item_tax = $product['unit_price'] * $actual_tax;
		$tax_amount = $total_invoice_item_tax * $quantity;
		$total_invoice_item = $total_invoice_item_tax + $product['unit_price'] ;	
		$total = $total_invoice_item * $quantity;
		$gross_total = $product['unit_price']  * $quantity;
		
		if ($db_server == 'mysql' && !_invoice_items_check_fk(
			$invoice_id, $product_id, $tax['tax_id'])) {
			return null;
		}
		$sql = "INSERT INTO ".TB_PREFIX."invoice_items (invoice_id, quantity, product_id, unit_price, tax_id, tax, tax_amount, gross_total, description, total, attribute_1, attribute_2, attribute_3) VALUES (:invoice_id, :quantity, :product_id, :product_price, :tax_id, :tax_percentage, :tax_amount, :gross_total, :description, :total ,:attribute_1, :attribute_2, :attribute_3 )";

		//echo $sql;
		return dbQuery($sql,
			':invoice_id', $invoice_id,
			':quantity', $quantity,
			':product_id', $product_id,
			':product_price', $product[unit_price],
			':tax_id', $tax[tax_id],
			':tax_percentage', $tax[tax_percentage],
			':tax_amount', $tax_amount,
			':gross_total', $gross_total,
			':description', $description,
			':total', $total,
			':attribute_1', $attr1,
			':attribute_2', $attr2,
			':attribute_3', $attr3
			);

	}

	function updateInvoiceItem($id,$quantity,$product_id,$tax_id,$description) {

		$product = getProduct($product_id);
		$tax = getTaxRate($tax_id);
		
		$total_invoice_item_tax = $product['unit_price'] * $tax['tax_percentage'] / 100;	//:100?
		$tax_amount = $total_invoice_item_tax * $quantity;
		$total_invoice_item = $total_invoice_item_tax + $product['unit_price'];
		$total = $total_invoice_item * $quantity;
		$gross_total = $product['unit_price'] * $quantity;
		
		if ($db_server == 'mysql' && !_invoice_items_check_fk(
			null, $product_id, $tax_id, 'update')) {
			return null;
		}

		$sql = "UPDATE ".TB_PREFIX."invoice_items 
		SET quantity =  :quantity,
		product_id = :product_id,
		unit_price = :unit_price,
		tax_id = :tax_id,
		tax = :tax,
		tax_amount = :tax_amount,
		gross_total = :gross_total,
		description = :description,
		total = :total			
		WHERE id = :id";
		
		//echo $sql;
			
		return dbQuery($sql,
			':quantity', $quantity,
			':product_id', $product_id,
			':unit_price', $product[unit_price],
			':tax_id', $tax_id,
			':tax', $tax[tax_percentage],
			':tax_amount', $tax_amount,
			':gross_total', $gross_total,
			':description', $description,
			':total', $total,
			':id', $id
			);
	}
}
?>