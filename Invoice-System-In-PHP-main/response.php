<?php


include_once('includes/config.php');

// Create database connection
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

// show PHP errors
ini_set('display_errors', 1);

// output any connection error
if ($mysqli->connect_error) {
    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
}

function buildAutofillCustomerPayload($row) {
	return array(
		'name' => isset($row['name']) ? $row['name'] : '',
		'email' => isset($row['email']) ? $row['email'] : '',
		'phone' => isset($row['phone']) ? $row['phone'] : '',
		'address_1' => isset($row['address_1']) ? $row['address_1'] : '',
		'address_2' => isset($row['address_2']) ? $row['address_2'] : '',
		'town' => isset($row['town']) ? $row['town'] : '',
		'county' => isset($row['county']) ? $row['county'] : '',
		'postcode' => isset($row['postcode']) ? $row['postcode'] : '',
		'name_ship' => isset($row['name_ship']) ? $row['name_ship'] : '',
		'address_1_ship' => isset($row['address_1_ship']) ? $row['address_1_ship'] : '',
		'address_2_ship' => isset($row['address_2_ship']) ? $row['address_2_ship'] : '',
		'town_ship' => isset($row['town_ship']) ? $row['town_ship'] : '',
		'county_ship' => isset($row['county_ship']) ? $row['county_ship'] : '',
		'postcode_ship' => isset($row['postcode_ship']) ? $row['postcode_ship'] : ''
	);
}

function getDefaultItServiceTemplates() {
	return array(
		array(
			'product_name' => 'Remote IT Support',
			'product_desc' => 'Remote troubleshooting, software fixes, and user support.',
			'product_price' => '650.00',
			'usage_count' => 0,
			'source_label' => 'IT service template'
		),
		array(
			'product_name' => 'On-Site IT Support',
			'product_desc' => 'Callout support for workstation, printer, and office IT issues.',
			'product_price' => '950.00',
			'usage_count' => 0,
			'source_label' => 'IT service template'
		),
		array(
			'product_name' => 'Network Setup And Troubleshooting',
			'product_desc' => 'Router, switch, Wi-Fi, and structured network support services.',
			'product_price' => '1800.00',
			'usage_count' => 0,
			'source_label' => 'IT service template'
		),
		array(
			'product_name' => 'Microsoft 365 Setup',
			'product_desc' => 'Mailbox setup, licensing, user onboarding, and tenant support.',
			'product_price' => '1200.00',
			'usage_count' => 0,
			'source_label' => 'IT service template'
		),
		array(
			'product_name' => 'Website Maintenance',
			'product_desc' => 'Website updates, uptime checks, backups, and minor content changes.',
			'product_price' => '1500.00',
			'usage_count' => 0,
			'source_label' => 'IT service template'
		)
	);
}

function getProductPriceByName($mysqli, $productName) {
	$price = '';
	$stmt = $mysqli->prepare("SELECT product_price FROM products WHERE product_name = ? ORDER BY product_id DESC LIMIT 1");
	if (!$stmt) {
		return '';
	}

	$stmt->bind_param('s', $productName);
	$stmt->execute();
	$stmt->bind_result($price);
	$found = $stmt->fetch();
	$stmt->close();

	if ($found && $price !== null && $price !== '') {
		return (string)$price;
	}

	$templates = getDefaultItServiceTemplates();
	foreach ($templates as $template) {
		if ($template['product_name'] === $productName) {
			return (string)$template['product_price'];
		}
	}

	return '';
}

function getServiceBundles($mysqli) {
	$bundleTemplates = array(
		array(
			'bundle_name' => 'Managed IT Monthly Plan',
			'bundle_desc' => 'Recurring support package for SMEs with maintenance and user support.',
			'items' => array(
				array('product_name' => 'Remote IT Support', 'qty' => 4),
				array('product_name' => 'Website Maintenance', 'qty' => 1)
			)
		),
		array(
			'bundle_name' => 'New Office Setup Package',
			'bundle_desc' => 'Network rollout and onboarding support for a new office or branch.',
			'items' => array(
				array('product_name' => 'Network Setup And Troubleshooting', 'qty' => 1),
				array('product_name' => 'On-Site IT Support', 'qty' => 2),
				array('product_name' => 'Microsoft 365 Setup', 'qty' => 1)
			)
		),
		array(
			'bundle_name' => 'Business Continuity Starter',
			'bundle_desc' => 'Preventive support and operational continuity services.',
			'items' => array(
				array('product_name' => 'Remote IT Support', 'qty' => 2),
				array('product_name' => 'On-Site IT Support', 'qty' => 1),
				array('product_name' => 'Website Maintenance', 'qty' => 1)
			)
		)
	);

	$bundles = array();
	foreach ($bundleTemplates as $bundle) {
		$total = 0;
		$items = array();

		foreach ($bundle['items'] as $item) {
			$price = getProductPriceByName($mysqli, $item['product_name']);
			$lineTotal = ((float)$price) * ((int)$item['qty']);
			$total += $lineTotal;
			$items[] = array(
				'product_name' => $item['product_name'],
				'qty' => (int)$item['qty'],
				'product_price' => $price,
				'line_total' => number_format($lineTotal, 2, '.', '')
			);
		}

		$bundles[] = array(
			'bundle_name' => $bundle['bundle_name'],
			'bundle_desc' => $bundle['bundle_desc'],
			'bundle_total' => number_format($total, 2, '.', ''),
			'items' => $items
		);
	}

	return $bundles;
}

function buildCustomerProfileFromRequest() {
	$customer = array(
		'name' => isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '',
		'email' => isset($_POST['customer_email']) ? trim($_POST['customer_email']) : '',
		'phone' => isset($_POST['customer_phone']) ? trim($_POST['customer_phone']) : '',
		'address_1' => isset($_POST['customer_address_1']) ? trim($_POST['customer_address_1']) : '',
		'address_2' => isset($_POST['customer_address_2']) ? trim($_POST['customer_address_2']) : '',
		'town' => isset($_POST['customer_town']) ? trim($_POST['customer_town']) : '',
		'county' => isset($_POST['customer_county']) ? trim($_POST['customer_county']) : '',
		'postcode' => isset($_POST['customer_postcode']) ? trim($_POST['customer_postcode']) : '',
		'name_ship' => isset($_POST['customer_name_ship']) ? trim($_POST['customer_name_ship']) : '',
		'address_1_ship' => isset($_POST['customer_address_1_ship']) ? trim($_POST['customer_address_1_ship']) : '',
		'address_2_ship' => isset($_POST['customer_address_2_ship']) ? trim($_POST['customer_address_2_ship']) : '',
		'town_ship' => isset($_POST['customer_town_ship']) ? trim($_POST['customer_town_ship']) : '',
		'county_ship' => isset($_POST['customer_county_ship']) ? trim($_POST['customer_county_ship']) : '',
		'postcode_ship' => isset($_POST['customer_postcode_ship']) ? trim($_POST['customer_postcode_ship']) : ''
	);

	if ($customer['name_ship'] === '') {
		$customer['name_ship'] = $customer['name'];
	}
	if ($customer['address_1_ship'] === '') {
		$customer['address_1_ship'] = $customer['address_1'];
	}
	if ($customer['address_2_ship'] === '') {
		$customer['address_2_ship'] = $customer['address_2'];
	}
	if ($customer['town_ship'] === '') {
		$customer['town_ship'] = $customer['town'];
	}
	if ($customer['county_ship'] === '') {
		$customer['county_ship'] = $customer['county'];
	}
	if ($customer['postcode_ship'] === '') {
		$customer['postcode_ship'] = $customer['postcode'];
	}

	return $customer;
}

function loadAutofillInvoiceItems($mysqli, $invoiceNumber) {
	$items = array();

	if (empty($invoiceNumber)) {
		return $items;
	}

	$itemQuery = "SELECT product, qty, price, discount, subtotal
				 FROM invoice_items
				 WHERE invoice = '".$mysqli->real_escape_string($invoiceNumber)."'
				 ORDER BY id ASC";
	$itemResult = $mysqli->query($itemQuery);

	if ($itemResult && $itemResult->num_rows > 0) {
		while ($row = $itemResult->fetch_assoc()) {
			$items[] = $row;
		}
	}

	return $items;
}

function loadSuggestedProducts($mysqli, $customer) {
	$suggestions = array();
	$customerEmail = isset($customer['email']) ? trim($customer['email']) : '';
	$customerName = isset($customer['name']) ? trim($customer['name']) : '';
	$defaultServiceTemplates = getDefaultItServiceTemplates();

	if ($customerEmail !== '' || $customerName !== '') {
		$historyConditions = array();
		if ($customerEmail !== '') {
			$historyConditions[] = "c.email = '".$mysqli->real_escape_string($customerEmail)."'";
		}
		if ($customerName !== '') {
			$historyConditions[] = "c.name = '".$mysqli->real_escape_string($customerName)."'";
		}

		$historyQuery = "SELECT ii.product,
					COALESCE(MAX(p.product_desc), '') AS product_desc,
					COALESCE(MAX(NULLIF(ii.price, '')), MAX(p.product_price), '') AS product_price,
					COUNT(*) AS usage_count
				 FROM customers c
				 JOIN invoice_items ii ON ii.invoice = c.invoice
				 LEFT JOIN products p ON ii.product LIKE CONCAT(p.product_name, '%')
				 WHERE ".implode(' OR ', $historyConditions)."
				 GROUP BY ii.product
				 ORDER BY usage_count DESC, ii.product ASC
				 LIMIT 5";
		$historyResult = $mysqli->query($historyQuery);

		if ($historyResult && $historyResult->num_rows > 0) {
			while ($row = $historyResult->fetch_assoc()) {
				$suggestions[] = array(
					'product_name' => $row['product'],
					'product_desc' => $row['product_desc'],
					'product_price' => $row['product_price'],
					'usage_count' => (int)$row['usage_count'],
					'source_label' => 'Client history'
				);
			}
		}
	}

	if (count($suggestions) === 0) {
		$productResult = $mysqli->query("SELECT product_name, product_desc, product_price FROM products ORDER BY product_name ASC LIMIT 5");
		if ($productResult && $productResult->num_rows > 0) {
			while ($row = $productResult->fetch_assoc()) {
				$suggestions[] = array(
					'product_name' => $row['product_name'],
					'product_desc' => $row['product_desc'],
					'product_price' => $row['product_price'],
					'usage_count' => 0,
					'source_label' => 'Product catalog'
				);
			}
		}
	}

	if (count($suggestions) === 0) {
		$suggestions = $defaultServiceTemplates;
	}

	return $suggestions;
}

function calculateAutofillScore($row, $customerEmail, $customerName, $hasInvoiceItems) {
	$score = 0;
	$rowEmail = isset($row['email']) ? strtolower(trim($row['email'])) : '';
	$rowName = isset($row['name']) ? strtolower(trim($row['name'])) : '';
	$searchEmail = strtolower(trim($customerEmail));
	$searchName = strtolower(trim($customerName));

	if ($searchEmail !== '' && $rowEmail === $searchEmail) {
		$score += 100;
	}

	if ($searchName !== '' && $rowName === $searchName) {
		$score += 70;
	} elseif ($searchName !== '' && $rowName !== '' && strpos($rowName, $searchName) !== false) {
		$score += 40;
	}

	if (!empty($hasInvoiceItems)) {
		$score += 20;
	}

	if (!empty($row['phone'])) {
		$score += 5;
	}

	return $score;
}

function appendAutofillMatches(&$matches, &$seenKeys, $result, $sourceLabel, $customerEmail, $customerName, $mysqli) {
	if (!$result) {
		return;
	}

	while ($row = $result->fetch_assoc()) {
		$dedupeKey = strtolower(trim((isset($row['email']) ? $row['email'] : ''))).'|'.strtolower(trim((isset($row['name']) ? $row['name'] : '')));
		if ($dedupeKey === '|') {
			$dedupeKey = $sourceLabel.'|'.(isset($row['source_id']) ? $row['source_id'] : uniqid('', true));
		}
		if (isset($seenKeys[$dedupeKey])) {
			continue;
		}

		$seenKeys[$dedupeKey] = true;
		$customer = buildAutofillCustomerPayload($row);
		$invoiceNumber = isset($row['invoice']) ? $row['invoice'] : '';
		$items = loadAutofillInvoiceItems($mysqli, $invoiceNumber);
		$locationParts = array_filter(array(
			isset($row['town']) ? $row['town'] : '',
			isset($row['county']) ? $row['county'] : '',
			isset($row['postcode']) ? $row['postcode'] : ''
		));

		$matches[] = array(
			'source_label' => $sourceLabel,
			'source_id' => isset($row['source_id']) ? $row['source_id'] : '',
			'invoice_number' => $invoiceNumber,
			'invoice_date' => isset($row['invoice_date']) ? $row['invoice_date'] : '',
			'display_name' => isset($row['name']) ? $row['name'] : '',
			'display_email' => isset($row['email']) ? $row['email'] : '',
			'display_location' => implode(', ', $locationParts),
			'match_score' => calculateAutofillScore($row, $customerEmail, $customerName, count($items) > 0),
			'customer' => $customer,
			'items' => $items,
			'suggestions' => loadSuggestedProducts($mysqli, $customer)
		);
	}
}

$action = isset($_POST['action']) ? $_POST['action'] : "";

if ($action == 'get_service_bundles') {
	header('Content-Type: application/json');

	echo json_encode(array(
		'status' => 'Success',
		'message' => 'Service bundles loaded.',
		'data' => array(
			'bundles' => getServiceBundles($mysqli)
		)
	));
	exit;
}

if ($action == 'seed_it_service_products') {
	header('Content-Type: application/json');

	$templates = getDefaultItServiceTemplates();
	$added = 0;
	$skipped = 0;

	$checkStmt = $mysqli->prepare("SELECT COUNT(*) FROM products WHERE product_name = ?");
	$insertStmt = $mysqli->prepare("INSERT INTO products (product_name, product_desc, product_price) VALUES (?, ?, ?)");

	if (!$checkStmt || !$insertStmt) {
		echo json_encode(array(
			'status' => 'Error',
			'message' => 'Unable to prepare product template statements.'
		));
		exit;
	}

	foreach ($templates as $template) {
		$productName = $template['product_name'];
		$productDesc = $template['product_desc'];
		$productPrice = $template['product_price'];

		$exists = 0;
		$checkStmt->bind_param('s', $productName);
		$checkStmt->execute();
		$checkStmt->bind_result($exists);
		$checkStmt->fetch();
		$checkStmt->free_result();

		if ((int)$exists > 0) {
			$skipped++;
			continue;
		}

		$insertStmt->bind_param('sss', $productName, $productDesc, $productPrice);
		if ($insertStmt->execute()) {
			$added++;
		}
	}

	$checkStmt->close();
	$insertStmt->close();

	echo json_encode(array(
		'status' => 'Success',
		'message' => 'IT service catalog templates processed.',
		'data' => array(
			'added' => $added,
			'skipped' => $skipped,
			'templates' => getDefaultItServiceTemplates()
		)
	));
	exit;
}

if ($action == 'save_invoice_customer_profile') {
	header('Content-Type: application/json');

	$customer = buildCustomerProfileFromRequest();
	if ($customer['name'] === '' || $customer['email'] === '') {
		echo json_encode(array(
			'status' => 'Error',
			'message' => 'Customer name and email are required before saving a client profile.'
		));
		exit;
	}

	$existingId = 0;
	$checkStmt = $mysqli->prepare("SELECT id FROM store_customers WHERE email = ? OR name = ? ORDER BY id DESC LIMIT 1");
	$checkStmt->bind_param('ss', $customer['email'], $customer['name']);
	$checkStmt->execute();
	$checkStmt->bind_result($existingId);
	$checkStmt->fetch();
	$checkStmt->close();

	if ($existingId) {
		$updateStmt = $mysqli->prepare("UPDATE store_customers SET name = ?, email = ?, address_1 = ?, address_2 = ?, town = ?, county = ?, postcode = ?, phone = ?, name_ship = ?, address_1_ship = ?, address_2_ship = ?, town_ship = ?, county_ship = ?, postcode_ship = ? WHERE id = ?");
		$updateStmt->bind_param(
			'ssssssssssssssi',
			$customer['name'],
			$customer['email'],
			$customer['address_1'],
			$customer['address_2'],
			$customer['town'],
			$customer['county'],
			$customer['postcode'],
			$customer['phone'],
			$customer['name_ship'],
			$customer['address_1_ship'],
			$customer['address_2_ship'],
			$customer['town_ship'],
			$customer['county_ship'],
			$customer['postcode_ship'],
			$existingId
		);
		$success = $updateStmt->execute();
		$updateStmt->close();

		echo json_encode(array(
			'status' => $success ? 'Success' : 'Error',
			'message' => $success ? 'Client profile updated successfully.' : 'Unable to update client profile.'
		));
		exit;
	}

	$insertStmt = $mysqli->prepare("INSERT INTO store_customers (name, email, address_1, address_2, town, county, postcode, phone, name_ship, address_1_ship, address_2_ship, town_ship, county_ship, postcode_ship) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
	$insertStmt->bind_param(
		'ssssssssssssss',
		$customer['name'],
		$customer['email'],
		$customer['address_1'],
		$customer['address_2'],
		$customer['town'],
		$customer['county'],
		$customer['postcode'],
		$customer['phone'],
		$customer['name_ship'],
		$customer['address_1_ship'],
		$customer['address_2_ship'],
		$customer['town_ship'],
		$customer['county_ship'],
		$customer['postcode_ship']
	);
	$success = $insertStmt->execute();
	$insertStmt->close();

	echo json_encode(array(
		'status' => $success ? 'Success' : 'Error',
		'message' => $success ? 'Client profile saved successfully.' : 'Unable to save client profile.'
	));
	exit;
}

if ($action == 'auto_fill_invoice') {
	header('Content-Type: application/json');

	$customer_email = isset($_POST['customer_email']) ? trim($_POST['customer_email']) : '';
	$customer_name = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '';

	if ($customer_email === '' && $customer_name === '') {
		echo json_encode(array(
			'status' => 'Error',
			'message' => 'Please provide customer email or name to auto fill.'
		));
		exit;
	}

	$conditions = array();
	if ($customer_email !== '') {
		$conditions[] = "c.email = '".$mysqli->real_escape_string($customer_email)."'";
	}
	if ($customer_name !== '') {
		$conditions[] = "c.name LIKE '%".$mysqli->real_escape_string($customer_name)."%'";
	}

	$whereClause = implode(' OR ', $conditions);
	$matches = array();
	$seenKeys = array();

	$query = "SELECT c.*, c.id AS source_id, i.invoice, i.invoice_date
				FROM customers c
				LEFT JOIN invoices i ON i.invoice = c.invoice
				WHERE ".$whereClause."
				ORDER BY c.id DESC
				LIMIT 6";
	$customerResult = $mysqli->query($query);
	appendAutofillMatches($matches, $seenKeys, $customerResult, 'Previous invoice', $customer_email, $customer_name, $mysqli);

	$fallbackConditions = array();
	if ($customer_email !== '') {
		$fallbackConditions[] = "email = '".$mysqli->real_escape_string($customer_email)."'";
	}
	if ($customer_name !== '') {
		$fallbackConditions[] = "name LIKE '%".$mysqli->real_escape_string($customer_name)."%'";
	}

	$fallbackWhere = implode(' OR ', $fallbackConditions);
	$fallbackQuery = "SELECT *, id AS source_id FROM store_customers WHERE ".$fallbackWhere." ORDER BY id DESC LIMIT 6";
	$fallbackResult = $mysqli->query($fallbackQuery);
	appendAutofillMatches($matches, $seenKeys, $fallbackResult, 'Saved customer', $customer_email, $customer_name, $mysqli);

	usort($matches, function ($a, $b) {
		return $b['match_score'] - $a['match_score'];
	});
	$matches = array_slice($matches, 0, 3);

	if (count($matches) === 0) {
		echo json_encode(array(
			'status' => 'Error',
			'message' => 'No matching customer record found for auto fill.'
		));
		exit;
	}

	echo json_encode(array(
		'status' => 'Success',
		'message' => count($matches) === 1 ? '1 matching profile found.' : 'Select the right client profile to auto fill.',
		'data' => array(
			'matches' => $matches
		)
	));
	exit;
}

if ($action == 'email_invoice'){

	$fileId = $_POST['id'];
	$emailId = $_POST['email'];
	$invoice_type = $_POST['invoice_type'];
	$custom_email = $_POST['custom_email'];

	require_once('class.phpmailer.php');

	$mail = new PHPMailer(); // defaults to using php "mail()"

	$mail->AddReplyTo(EMAIL_FROM, EMAIL_NAME);
	$mail->SetFrom(EMAIL_FROM, EMAIL_NAME);
	$mail->AddAddress($emailId, "");

	$mail->Subject = EMAIL_SUBJECT;
	//$mail->AltBody = EMAIL_BODY; // optional, comment out and test
	if (empty($custom_email)){
		if($invoice_type == 'invoice'){
			$mail->MsgHTML(EMAIL_BODY_INVOICE);
		} else if($invoice_type == 'quote'){
			$mail->MsgHTML(EMAIL_BODY_QUOTE);
		} else if($invoice_type == 'receipt'){
			$mail->MsgHTML(EMAIL_BODY_RECEIPT);
		}
	} else {
		$mail->MsgHTML($custom_email);
	}

	$mail->AddAttachment("./invoices/".$fileId.".pdf"); // attachment

	if(!$mail->Send()) {
		 //if unable to create new record
	    echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'There has been an error, please try again.<pre>'.$mail->ErrorInfo.'</pre>'
	    ));
	} else {
	   echo json_encode(array(
			'status' => 'Success',
			'message'=> 'Invoice has been successfully send to the customer'
		));
	}

}
// download invoice csv sheet
if ($action == 'download_csv'){

	// output any connection error
	if ($mysqli->connect_error) {
		echo json_encode(array(
			'status' => 'Error',
			'message'=> 'Database connection error: ('.$mysqli->connect_errno .') '. $mysqli->connect_error
		));
		exit;
	}
 
    $file_name = 'invoice-export-'.date('d-m-Y').'.csv';   // file name
    $file_path = 'downloads/'.$file_name; // file path

	// Check if downloads directory exists, create if not
	if (!file_exists('downloads')) {
		mkdir('downloads', 0777, true);
	}

	// Open file in write mode - check if successful
	$file = fopen($file_path, "w");
	if (!$file) {
		echo json_encode(array(
			'status' => 'Error',
			'message'=> 'Failed to create CSV file. Please check write permissions on downloads folder.'
		));
		exit;
	}

    // Set file permissions (only if file was created successfully)
    chmod($file_path, 0777);

    // Query to get invoice data with customer information
    $query_table_columns_data = "SELECT * 
									FROM invoices i
									JOIN customers c
									ON c.invoice = i.invoice
									WHERE i.invoice = c.invoice
									ORDER BY i.invoice";

    $result_column_data = mysqli_query($mysqli, $query_table_columns_data);

    // Check if query was successful
    if (!$result_column_data) {
		fclose($file);
	    echo json_encode(array(
	    	'status' => 'Error',
	    	'message' => 'Database query failed: <pre>'.$mysqli->error.'</pre>'
	    ));
	    exit;
	}

	// Write CSV column headers (field names)
	$first_row = true;
	if (mysqli_num_rows($result_column_data) > 0) {
		// Get field information for headers
		$field_info = mysqli_fetch_fields($result_column_data);
		$headers = array();
		foreach ($field_info as $field) {
			$headers[] = $field->name;
		}
		// Write headers to CSV
		fputcsv($file, $headers, ",", '"');
	}

	// Write data rows to CSV
	$row_count = 0;
	while ($column_data = $result_column_data->fetch_row()) {
		$table_column_data = array();
		foreach($column_data as $data) {
			// Ensure data is properly formatted for CSV (handle NULL values)
			$table_column_data[] = ($data !== null) ? $data : '';
		}
		
		// Format array as CSV and write to file pointer
		fputcsv($file, $table_column_data, ",", '"');
		$row_count++;
	}

	// Close file pointer
	fclose($file);

    // Return success response with download information
	echo json_encode(array(
		'status' => 'Success',
		'message'=> 'CSV has been generated with '.$row_count.' invoice records.',
		'download_url' => 'downloads/'.$file_name,
		'file_name' => $file_name,
		'row_count' => $row_count
	));

    $mysqli->close();

}

// Create customer
if ($action == 'create_customer'){

	// invoice customer information
	// billing
	$customer_name = $_POST['customer_name']; // customer name
	$customer_email = $_POST['customer_email']; // customer email
	$customer_address_1 = $_POST['customer_address_1']; // customer address
	$customer_address_2 = $_POST['customer_address_2']; // customer address
	$customer_town = $_POST['customer_town']; // customer town
	$customer_county = $_POST['customer_county']; // customer county
	$customer_postcode = $_POST['customer_postcode']; // customer postcode
	$customer_phone = $_POST['customer_phone']; // customer phone number
	
	//shipping
	$customer_name_ship = $_POST['customer_name_ship']; // customer name (shipping)
	$customer_address_1_ship = $_POST['customer_address_1_ship']; // customer address (shipping)
	$customer_address_2_ship = $_POST['customer_address_2_ship']; // customer address (shipping)
	$customer_town_ship = $_POST['customer_town_ship']; // customer town (shipping)
	$customer_county_ship = $_POST['customer_county_ship']; // customer county (shipping)
	$customer_postcode_ship = $_POST['customer_postcode_ship']; // customer postcode (shipping)

	$query = "INSERT INTO store_customers (
					name,
					email,
					address_1,
					address_2,
					town,
					county,
					postcode,
					phone,
					name_ship,
					address_1_ship,
					address_2_ship,
					town_ship,
					county_ship,
					postcode_ship
				) VALUES (
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?
				);
			";

	/* Prepare statement */
	$stmt = $mysqli->prepare($query);
	if($stmt === false) {
	  trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
	}

	/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
	$stmt->bind_param(
		'ssssssssssssss',
		$customer_name,$customer_email,$customer_address_1,$customer_address_2,$customer_town,$customer_county,$customer_postcode,
		$customer_phone,$customer_name_ship,$customer_address_1_ship,$customer_address_2_ship,$customer_town_ship,$customer_county_ship,$customer_postcode_ship);

	if($stmt->execute()){
		//if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message' => 'Customer has been created successfully!'
		));
	} else {
		// if unable to create invoice
		echo json_encode(array(
			'status' => 'Error',
			'message' => 'There has been an error, please try again.'
			// debug
			//'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
		));
	}

	//close database connection
	$mysqli->close();
}

// Create invoice
if ($action == 'create_invoice'){

	// invoice customer information
	// billing
	$customer_name = $_POST['customer_name']; // customer name
	$customer_email = $_POST['customer_email']; // customer email
	$customer_address_1 = $_POST['customer_address_1']; // customer address
	$customer_address_2 = $_POST['customer_address_2']; // customer address
	$customer_town = $_POST['customer_town']; // customer town
	$customer_county = $_POST['customer_county']; // customer county
	$customer_postcode = $_POST['customer_postcode']; // customer postcode
	$customer_phone = $_POST['customer_phone']; // customer phone number
	
	//shipping
	$customer_name_ship = $_POST['customer_name_ship']; // customer name (shipping)
	$customer_address_1_ship = $_POST['customer_address_1_ship']; // customer address (shipping)
	$customer_address_2_ship = $_POST['customer_address_2_ship']; // customer address (shipping)
	$customer_town_ship = $_POST['customer_town_ship']; // customer town (shipping)
	$customer_county_ship = $_POST['customer_county_ship']; // customer county (shipping)
	$customer_postcode_ship = $_POST['customer_postcode_ship']; // customer postcode (shipping)

	// invoice details
	$invoice_number = $_POST['invoice_id']; // invoice number
	$custom_email = $_POST['custom_email']; // invoice custom email body
	$invoice_date = $_POST['invoice_date']; // invoice date
	$custom_email = $_POST['custom_email']; // custom invoice email
	$invoice_due_date = $_POST['invoice_due_date']; // invoice due date
	$invoice_subtotal = $_POST['invoice_subtotal']; // invoice sub-total
	$invoice_shipping = $_POST['invoice_shipping']; // invoice shipping amount
	$invoice_discount = $_POST['invoice_discount']; // invoice discount
	$invoice_vat = $_POST['invoice_vat']; // invoice vat
	$invoice_total = $_POST['invoice_total']; // invoice total
	$invoice_notes = $_POST['invoice_notes']; // Invoice notes
	$invoice_type = $_POST['invoice_type']; // Invoice type
	$invoice_status = $_POST['invoice_status']; // Invoice status

	// insert invoice into database
	$query = "INSERT INTO invoices (
					invoice,
					custom_email,
					invoice_date, 
					invoice_due_date, 
					subtotal, 
					shipping, 
					discount, 
					vat, 
					total,
					notes,
					invoice_type,
					status
				) VALUES (
				  	'".$invoice_number."',
				  	'".$custom_email."',
				  	'".$invoice_date."',
				  	'".$invoice_due_date."',
				  	'".$invoice_subtotal."',
				  	'".$invoice_shipping."',
				  	'".$invoice_discount."',
				  	'".$invoice_vat."',
				  	'".$invoice_total."',
				  	'".$invoice_notes."',
				  	'".$invoice_type."',
				  	'".$invoice_status."'
			    );
			";
	// insert customer details into database
	$query .= "INSERT INTO customers (
					invoice,
					name,
					email,
					address_1,
					address_2,
					town,
					county,
					postcode,
					phone,
					name_ship,
					address_1_ship,
					address_2_ship,
					town_ship,
					county_ship,
					postcode_ship
				) VALUES (
					'".$invoice_number."',
					'".$customer_name."',
					'".$customer_email."',
					'".$customer_address_1."',
					'".$customer_address_2."',
					'".$customer_town."',
					'".$customer_county."',
					'".$customer_postcode."',
					'".$customer_phone."',
					'".$customer_name_ship."',
					'".$customer_address_1_ship."',
					'".$customer_address_2_ship."',
					'".$customer_town_ship."',
					'".$customer_county_ship."',
					'".$customer_postcode_ship."'
				);
			";

	// invoice product items
	foreach($_POST['invoice_product'] as $key => $value) {
	    $item_product = $value;
	    // $item_description = $_POST['invoice_product_desc'][$key];
	    $item_qty = $_POST['invoice_product_qty'][$key];
	    $item_price = $_POST['invoice_product_price'][$key];
	    $item_discount = $_POST['invoice_product_discount'][$key];
	    $item_subtotal = $_POST['invoice_product_sub'][$key];

	    // insert invoice items into database
		$query .= "INSERT INTO invoice_items (
				invoice,
				product,
				qty,
				price,
				discount,
				subtotal
			) VALUES (
				'".$invoice_number."',
				'".$item_product."',
				'".$item_qty."',
				'".$item_price."',
				'".$item_discount."',
				'".$item_subtotal."'
			);
		";

	}

	header('Content-Type: application/json');

	// execute the query
	if($mysqli -> multi_query($query)){
		//if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message' => 'Invoice has been created successfully!'
		));

		//Set default date timezone
		date_default_timezone_set(TIMEZONE);
		//Include Invoicr class
		include('invoice.php');
		//Create a new instance
		$invoice = new invoicr("A4",CURRENCY,"en");
		//Set number formatting
		$invoice->setNumberFormat('.',',');
		//Set your logo
		$invoice->setLogo(COMPANY_LOGO_PDF,COMPANY_LOGO_WIDTH,COMPANY_LOGO_HEIGHT);
		//Set theme color
		$invoice->setColor(INVOICE_THEME);
		//Set type
		$invoice->setType($invoice_type);
		//Set reference
		$invoice->setReference($invoice_number);
		//Set date
		$invoice->setDate($invoice_date);
		//Set due date
		$invoice->setDue($invoice_due_date);
		//Set from
		$invoice->setFrom(array(COMPANY_NAME,COMPANY_ADDRESS_1,COMPANY_ADDRESS_2,COMPANY_COUNTY,COMPANY_POSTCODE,COMPANY_NUMBER,COMPANY_VAT));
		//Set to
		$invoice->setTo(array($customer_name,$customer_address_1,$customer_address_2,$customer_town,$customer_county,$customer_postcode,"Phone: ".$customer_phone));
		//Ship to
		$invoice->shipTo(array($customer_name_ship,$customer_address_1_ship,$customer_address_2_ship,$customer_town_ship,$customer_county_ship,$customer_postcode_ship,''));
		//Add items
		// invoice product items
		foreach($_POST['invoice_product'] as $key => $value) {
		    $item_product = $value;
		    // $item_description = $_POST['invoice_product_desc'][$key];
		    $item_qty = $_POST['invoice_product_qty'][$key];
		    $item_price = $_POST['invoice_product_price'][$key];
		    $item_discount = $_POST['invoice_product_discount'][$key];
		    $item_subtotal = $_POST['invoice_product_sub'][$key];

		   	$item_vat = 0;
		   	if(ENABLE_VAT == true) {
		   		$item_vat = (VAT_RATE / 100) * $item_subtotal;
		   	}

		    $invoice->addItem($item_product,'',$item_qty,$item_vat,$item_price,$item_subtotal,$item_discount);
		}
		//Add totals
		$invoice->addTotal("Total",$invoice_subtotal);
		if(!empty($invoice_discount)) {
			$invoice->addTotal("Discount",$invoice_discount);
		}
		if(!empty($invoice_shipping)) {
			$invoice->addTotal("Delivery",$invoice_shipping);
		}
		if(ENABLE_VAT == true) {
			$invoice->addTotal("TAX/VAT ".VAT_RATE."%",$invoice_vat);
		}
		$invoice->addTotal("Total Due",$invoice_total,true);
		//Add Badge
		$invoice->addBadge($invoice_status);
		// Customer notes:
		if(!empty($invoice_notes)) {
			$invoice->addTitle("Customer Notes");
			$invoice->addParagraph($invoice_notes);
		}
		//Add Title
		$invoice->addTitle("Payment information");
		//Add Paragraph
		$invoice->addParagraph(PAYMENT_DETAILS);
		//Set footer note
		$invoice->setFooternote(FOOTER_NOTE);
		//Render the PDF
		$invoice->render('invoices/'.$invoice_number.'.pdf','F');
	} else {
		header('Content-Type: application/json');
		// if unable to create invoice
		echo json_encode(array(
			'status' => 'Error',
			'message' => 'There has been an error, please try again. '.$mysqli->error
			// debug
			//'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
		));
	}

	//close database connection
	$mysqli->close();

}

// Adding new product
if($action == 'delete_invoice') {

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

	$id = $_POST["delete"];

	// the query
	$query = "DELETE FROM invoices WHERE invoice = ".$id.";";
	$query .= "DELETE FROM customers WHERE invoice = ".$id.";";
	$query .= "DELETE FROM invoice_items WHERE invoice = ".$id.";";

	unlink('invoices/'.$id.'.pdf');

	if($mysqli -> multi_query($query)) {
	    //if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'Product has been deleted successfully!'
		));

	} else {
	    //if unable to create new record
	    echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
	    ));
	}

	// close connection 
	$mysqli->close();

}

// Adding new product
if($action == 'update_customer') {

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

	$getID = $_POST['id']; // id

	// invoice customer information
	// billing
	$customer_name = $_POST['customer_name']; // customer name
	$customer_email = $_POST['customer_email']; // customer email
	$customer_address_1 = $_POST['customer_address_1']; // customer address
	$customer_address_2 = $_POST['customer_address_2']; // customer address
	$customer_town = $_POST['customer_town']; // customer town
	$customer_county = $_POST['customer_county']; // customer county
	$customer_postcode = $_POST['customer_postcode']; // customer postcode
	$customer_phone = $_POST['customer_phone']; // customer phone number
	
	//shipping
	$customer_name_ship = $_POST['customer_name_ship']; // customer name (shipping)
	$customer_address_1_ship = $_POST['customer_address_1_ship']; // customer address (shipping)
	$customer_address_2_ship = $_POST['customer_address_2_ship']; // customer address (shipping)
	$customer_town_ship = $_POST['customer_town_ship']; // customer town (shipping)
	$customer_county_ship = $_POST['customer_county_ship']; // customer county (shipping)
	$customer_postcode_ship = $_POST['customer_postcode_ship']; // customer postcode (shipping)

	// the query
	$query = "UPDATE store_customers SET
				name = ?,
				email = ?,
				address_1 = ?,
				address_2 = ?,
				town = ?,
				county = ?,
				postcode = ?,
				phone = ?,

				name_ship = ?,
				address_1_ship = ?,
				address_2_ship = ?,
				town_ship = ?,
				county_ship = ?,
				postcode_ship = ?

				WHERE id = ?

			";

	/* Prepare statement */
	$stmt = $mysqli->prepare($query);
	if($stmt === false) {
	  trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
	}

	/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
	$stmt->bind_param(
		'sssssssssssssss',
		$customer_name,$customer_email,$customer_address_1,$customer_address_2,$customer_town,$customer_county,$customer_postcode,
		$customer_phone,$customer_name_ship,$customer_address_1_ship,$customer_address_2_ship,$customer_town_ship,$customer_county_ship,$customer_postcode_ship,$getID);

	//execute the query
	if($stmt->execute()){
	    //if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'Customer has been updated successfully!'
		));

	} else {
	    //if unable to create new record
	    echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
	    ));
	}

	//close database connection
	$mysqli->close();
	
}

// Update product
if($action == 'update_product') {

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

	// invoice product information
	$getID = $_POST['id']; // id
	$product_name = $_POST['product_name']; // product name
	$product_desc = $_POST['product_desc']; // product desc
	$product_price = $_POST['product_price']; // product price

	// the query
	$query = "UPDATE products SET
				product_name = ?,
				product_desc = ?,
				product_price = ?
			 WHERE product_id = ?
			";

	/* Prepare statement */
	$stmt = $mysqli->prepare($query);
	if($stmt === false) {
	  trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
	}

	/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
	$stmt->bind_param(
		'ssss',
		$product_name,$product_desc,$product_price,$getID
	);

	//execute the query
	if($stmt->execute()){
	    //if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'Product has been updated successfully!'
		));

	} else {
	    //if unable to create new record
	    echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
	    ));
	}

	//close database connection
	$mysqli->close();
	
}


// Adding new product
if($action == 'update_invoice') {

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

	$id = $_POST["update_id"];

	// the query
	$query = "DELETE FROM invoices WHERE invoice = ".$id.";";
	//$query .= "DELETE FROM customers WHERE invoice = ".$id.";";
	$query .= "DELETE FROM invoice_items WHERE invoice = ".$id.";";

	unlink('invoices/'.$id.'.pdf');

	// invoice customer information
	// billing
	$customer_name = $_POST['customer_name']; // customer name
	$customer_email = $_POST['customer_email']; // customer email
	$customer_address_1 = $_POST['customer_address_1']; // customer address
	$customer_address_2 = $_POST['customer_address_2']; // customer address
	$customer_town = $_POST['customer_town']; // customer town
	$customer_county = $_POST['customer_county']; // customer county
	$customer_postcode = $_POST['customer_postcode']; // customer postcode
	$customer_phone = $_POST['customer_phone']; // customer phone number
	
	//shipping
	$customer_name_ship = $_POST['customer_name_ship']; // customer name (shipping)
	$customer_address_1_ship = $_POST['customer_address_1_ship']; // customer address (shipping)
	$customer_address_2_ship = $_POST['customer_address_2_ship']; // customer address (shipping)
	$customer_town_ship = $_POST['customer_town_ship']; // customer town (shipping)
	$customer_county_ship = $_POST['customer_county_ship']; // customer county (shipping)
	$customer_postcode_ship = $_POST['customer_postcode_ship']; // customer postcode (shipping)

	// invoice details
	$invoice_number = $_POST['invoice_id']; // invoice number
	$custom_email = $_POST['custom_email']; // invoice custom email body
	$invoice_date = $_POST['invoice_date']; // invoice date
	$invoice_due_date = $_POST['invoice_due_date']; // invoice due date
	$invoice_subtotal = $_POST['invoice_subtotal']; // invoice sub-total
	$invoice_shipping = $_POST['invoice_shipping']; // invoice shipping amount
	$invoice_discount = $_POST['invoice_discount']; // invoice discount
	$invoice_vat = $_POST['invoice_vat']; // invoice vat
	$invoice_total = $_POST['invoice_total']; // invoice total
	$invoice_notes = $_POST['invoice_notes']; // Invoice notes
	$invoice_type = $_POST['invoice_type']; // Invoice type
	$invoice_status = $_POST['invoice_status']; // Invoice status

	// insert invoice into database
	$query .= "INSERT INTO invoices (
					invoice, 
					invoice_date, 
					invoice_due_date, 
					subtotal, 
					shipping, 
					discount, 
					vat, 
					total,
					notes,
					invoice_type,
					status
				) VALUES (
				  	'".$invoice_number."',
				  	'".$invoice_date."',
				  	'".$invoice_due_date."',
				  	'".$invoice_subtotal."',
				  	'".$invoice_shipping."',
				  	'".$invoice_discount."',
				  	'".$invoice_vat."',
				  	'".$invoice_total."',
				  	'".$invoice_notes."',
				  	'".$invoice_type."',
				  	'".$invoice_status."'
			    );
			";
	// insert customer details into database
	$query .= "INSERT INTO customers (
					invoice,
					custom_email,
					name,
					email,
					address_1,
					address_2,
					town,
					county,
					postcode,
					phone,
					name_ship,
					address_1_ship,
					address_2_ship,
					town_ship,
					county_ship,
					postcode_ship
				) VALUES (
					'".$invoice_number."',
					'".$custom_email."',
					'".$customer_name."',
					'".$customer_email."',
					'".$customer_address_1."',
					'".$customer_address_2."',
					'".$customer_town."',
					'".$customer_county."',
					'".$customer_postcode."',
					'".$customer_phone."',
					'".$customer_name_ship."',
					'".$customer_address_1_ship."',
					'".$customer_address_2_ship."',
					'".$customer_town_ship."',
					'".$customer_county_ship."',
					'".$customer_postcode_ship."'
				);
			";

	// invoice product items
	foreach($_POST['invoice_product'] as $key => $value) {
	    $item_product = $value;
	    // $item_description = $_POST['invoice_product_desc'][$key];
	    $item_qty = $_POST['invoice_product_qty'][$key];
	    $item_price = $_POST['invoice_product_price'][$key];
	    $item_discount = $_POST['invoice_product_discount'][$key];
	    $item_subtotal = $_POST['invoice_product_sub'][$key];

	    // insert invoice items into database
		$query .= "INSERT INTO invoice_items (
				invoice,
				product,
				qty,
				price,
				discount,
				subtotal
			) VALUES (
				'".$invoice_number."',
				'".$item_product."',
				'".$item_qty."',
				'".$item_price."',
				'".$item_discount."',
				'".$item_subtotal."'
			);
		";

	}

	header('Content-Type: application/json');

	if($mysqli -> multi_query($query)) {
	    //if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'Product has been updated successfully!'
		));

		//Set default date timezone
		date_default_timezone_set(TIMEZONE);
		//Include Invoicr class
		include('invoice.php');
		//Create a new instance
		$invoice = new invoicr("A4",CURRENCY,"en");
		//Set number formatting
		$invoice->setNumberFormat('.',',');
		//Set your logo
		$invoice->setLogo(COMPANY_LOGO_PDF,COMPANY_LOGO_WIDTH,COMPANY_LOGO_HEIGHT);
		//Set theme color
		$invoice->setColor(INVOICE_THEME);
		//Set type
		$invoice->setType("Invoice");
		//Set reference
		$invoice->setReference($invoice_number);
		//Set date
		$invoice->setDate($invoice_date);
		//Set due date
		$invoice->setDue($invoice_due_date);
		//Set from
		$invoice->setFrom(array(COMPANY_NAME,COMPANY_ADDRESS_1,COMPANY_ADDRESS_2,COMPANY_COUNTY,COMPANY_POSTCODE,COMPANY_NUMBER,COMPANY_VAT));
		//Set to
		$invoice->setTo(array($customer_name,$customer_address_1,$customer_address_2,$customer_town,$customer_county,$customer_postcode,"Phone: ".$customer_phone));
		//Ship to
		$invoice->shipTo(array($customer_name_ship,$customer_address_1_ship,$customer_address_2_ship,$customer_town_ship,$customer_county_ship,$customer_postcode_ship,''));
		//Add items
		// invoice product items
		foreach($_POST['invoice_product'] as $key => $value) {
		    $item_product = $value;
		    // $item_description = $_POST['invoice_product_desc'][$key];
		    $item_qty = $_POST['invoice_product_qty'][$key];
		    $item_price = $_POST['invoice_product_price'][$key];
		    $item_discount = $_POST['invoice_product_discount'][$key];
		    $item_subtotal = $_POST['invoice_product_sub'][$key];

		   	if(ENABLE_VAT == true) {
		   		$item_vat = (VAT_RATE / 100) * $item_subtotal;
		   	}

		    $invoice->addItem($item_product,'',$item_qty,$item_vat,$item_price,$item_subtotal,$item_discount);
		}
		//Add totals
		$invoice->addTotal("Total",$invoice_subtotal);
		if(!empty($invoice_discount)) {
			$invoice->addTotal("Discount",$invoice_discount);
		}
		if(!empty($invoice_shipping)) {
			$invoice->addTotal("Delivery",$invoice_shipping);
		}
		if(ENABLE_VAT == true) {
			$invoice->addTotal("TAX/VAT ".VAT_RATE."%",$invoice_vat);
		}
		$invoice->addTotal("Total Due",$invoice_total,true);
		//Add Badge
		$invoice->addBadge($invoice_status);
		// Customer notes:
		if(!empty($invoice_notes)) {
			$invoice->addTitle("Customer Notes");
			$invoice->addParagraph($invoice_notes);
		}
		//Add Title
		$invoice->addTitle("Payment information");
		//Add Paragraph
		$invoice->addParagraph(PAYMENT_DETAILS);
		//Set footer note
		$invoice->setFooternote(FOOTER_NOTE);
		//Render the PDF
		$invoice->render('invoices/'.$invoice_number.'.pdf','F');

	} else {
	    //if unable to create new record
	    echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
	    ));
	}

	// close connection 
	$mysqli->close();

}

// Adding new product
if($action == 'delete_product') {

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

	$id = $_POST["delete"];

	// the query
	$query = "DELETE FROM products WHERE product_id = ?";

	/* Prepare statement */
	$stmt = $mysqli->prepare($query);
	if($stmt === false) {
	  trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
	}

	/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
	$stmt->bind_param('s',$id);

	//execute the query
	if($stmt->execute()){
	    //if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'Product has been deleted successfully!'
		));

	} else {
	    //if unable to create new record
	    echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
	    ));
	}

	// close connection 
	$mysqli->close();

}

// Login to system
if($action == 'login') {

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

	session_start();

    extract($_POST);

    $username = mysqli_real_escape_string($mysqli,$_POST['username']);
    $pass_encrypt = md5(mysqli_real_escape_string($mysqli,$_POST['password']));

    $query = "SELECT * FROM `users` WHERE username='$username' AND `password` = '$pass_encrypt'";

    $results = mysqli_query($mysqli,$query) or die (mysqli_error());
    $count = mysqli_num_rows($results);

    if($count!="") {
		$row = $results->fetch_assoc();

		$_SESSION['login_username'] = $row['username'];

		// processing remember me option and setting cookie with long expiry date
		if (isset($_POST['remember'])) {	
			session_set_cookie_params('604800'); //one week (value in seconds)
			session_regenerate_id(true);
		}  
		
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'Login was a success! Transfering you to the system now, hold tight!'
		));
    } else {
    	echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'Login incorrect, does not exist or simply a problem! Try again!'
	    ));
    }
}

// Adding new product
if($action == 'add_product') {

	$product_name = $_POST['product_name'];
	$product_desc = $_POST['product_desc'];
	$product_price = $_POST['product_price'];

	//our insert query query
	$query  = "INSERT INTO products
				(
					product_name,
					product_desc,
					product_price
				)
				VALUES (
					?, 
                	?,
                	?
                );
              ";

    header('Content-Type: application/json');

	/* Prepare statement */
	$stmt = $mysqli->prepare($query);
	if($stmt === false) {
	  trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
	}

	/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
	$stmt->bind_param('sss',$product_name,$product_desc,$product_price);

	if($stmt->execute()){
	    //if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'Product has been added successfully!'
		));

	} else {
	    //if unable to create new record
	    echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
	    ));
	}

	//close database connection
	$mysqli->close();
}

// Adding new user
if($action == 'add_user') {

	$user_name = $_POST['name'];
	$user_username = $_POST['username'];
	$user_email = $_POST['email'];
	$user_phone = $_POST['phone'];
	$user_password = $_POST['password'];

	//our insert query query
	$query  = "INSERT INTO users
				(
					name,
					username,
					email,
					phone,
					password
				)
				VALUES (
					?,
					?, 
                	?,
                	?,
                	?
                );
              ";

    header('Content-Type: application/json');

	/* Prepare statement */
	$stmt = $mysqli->prepare($query);
	if($stmt === false) {
	  trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
	}

	$user_password = md5($user_password);
	/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
	$stmt->bind_param('sssss',$user_name,$user_username,$user_email,$user_phone,$user_password);

	if($stmt->execute()){
	    //if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'User has been added successfully!'
		));

	} else {
	    //if unable to create new record
	    echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
	    ));
	}

	//close database connection
	$mysqli->close();
}

// Update product
if($action == 'update_user') {

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

	// user information
	$getID = $_POST['id']; // id
	$name = $_POST['name']; // name
	$username = $_POST['username']; // username
	$email = $_POST['email']; // email
	$phone = $_POST['phone']; // phone
	$password = $_POST['password']; // password

	if($password == ''){
		// the query
		$query = "UPDATE users SET
					name = ?,
					username = ?,
					email = ?,
					phone = ?
				 WHERE id = ?
				";
	} else {
		// the query
		$query = "UPDATE users SET
					name = ?,
					username = ?,
					email = ?,
					phone = ?,
					password =?
				 WHERE id = ?
				";
	}

	/* Prepare statement */
	$stmt = $mysqli->prepare($query);
	if($stmt === false) {
	  trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
	}

	if($password == ''){
		/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
		$stmt->bind_param(
			'sssss',
			$name,$username,$email,$phone,$getID
		);
	} else {
		$password = md5($password);
		/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
		$stmt->bind_param(
			'ssssss',
			$name,$username,$email,$phone,$password,$getID
		);
	}

	//execute the query
	if($stmt->execute()){
	    //if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'User has been updated successfully!'
		));

	} else {
	    //if unable to create new record
	    echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
	    ));
	}

	//close database connection
	$mysqli->close();
	
}

// Delete User
if($action == 'delete_user') {

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

	$id = $_POST["delete"];

	// the query
	$query = "DELETE FROM users WHERE id = ?";

	/* Prepare statement */
	$stmt = $mysqli->prepare($query);
	if($stmt === false) {
	  trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
	}

	/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
	$stmt->bind_param('s',$id);

	if($stmt->execute()){
	    //if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'User has been deleted successfully!'
		));

	} else {
	    //if unable to create new record
	    echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
	    ));
	}

	// close connection 
	$mysqli->close();

}

// Delete User
if($action == 'delete_customer') {

	// output any connection error
	if ($mysqli->connect_error) {
	    die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
	}

	$id = $_POST["delete"];

	// the query
	$query = "DELETE FROM store_customers WHERE id = ?";

	/* Prepare statement */
	$stmt = $mysqli->prepare($query);
	if($stmt === false) {
	  trigger_error('Wrong SQL: ' . $query . ' Error: ' . $mysqli->error, E_USER_ERROR);
	}

	/* Bind parameters. TYpes: s = string, i = integer, d = double,  b = blob */
	$stmt->bind_param('s',$id);

	if($stmt->execute()){
	    //if saving success
		echo json_encode(array(
			'status' => 'Success',
			'message'=> 'Customer has been deleted successfully!'
		));

	} else {
	    //if unable to create new record
	    echo json_encode(array(
	    	'status' => 'Error',
	    	//'message'=> 'There has been an error, please try again.'
	    	'message' => 'There has been an error, please try again.<pre>'.$mysqli->error.'</pre><pre>'.$query.'</pre>'
	    ));
	}

	// close connection 
	$mysqli->close();

}

// record payment
if ($action == 'record_payment'){
	header('Content-Type: application/json');
	ini_set('display_errors', 0);

	if (!isset($_POST['invoice_id']) || !isset($_POST['amount'])) {
		echo json_encode(array(
			'status' => 'Error',
			'message' => 'Invoice ID and amount are required'
		));
		exit;
	}

	include_once('enhanced-functions.php');

	$invoice_id = $_POST['invoice_id'];
	$amount = $_POST['amount'];
	$payment_date = isset($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d');
	$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'Manual';
	$notes = isset($_POST['notes']) ? $_POST['notes'] : '';

	$result = recordPayment($invoice_id, $amount, $payment_date, $payment_method, $notes);

	if ($result['success']) {
		echo json_encode(array(
			'status' => 'Success',
			'message' => 'Payment recorded successfully',
			'new_balance' => $result['new_balance'],
			'status' => $result['status']
		));
	} else {
		echo json_encode(array(
			'status' => 'Error',
			'message' => $result['message']
		));
	}

	$mysqli->close();
	exit;
}

// send reminder
if ($action == 'send_reminder'){
	header('Content-Type: application/json');
	ini_set('display_errors', 0);

	if (!isset($_POST['invoice_id'])) {
		echo json_encode(array(
			'status' => 'Error',
			'message' => 'Invoice ID is required'
		));
		exit;
	}

	include_once('enhanced-functions.php');

	$invoice_id = $_POST['invoice_id'];

	// Get invoice and customer details
	$query = "SELECT i.*, c.name, c.email 
		FROM invoices i
		JOIN customers c ON i.invoice = c.invoice
		WHERE i.id = " . intval($invoice_id);

	if ($result = $mysqli->query($query)) {
		if ($result->num_rows > 0) {
			$invoice = $result->fetch_assoc();

			if (sendOverdueReminder($invoice_id, $invoice['email'])) {
				echo json_encode(array(
					'status' => 'Success',
					'message' => 'Reminder sent to ' . $invoice['email']
				));
			} else {
				echo json_encode(array(
					'status' => 'Error',
					'message' => 'Failed to send reminder'
				));
			}
		} else {
			echo json_encode(array(
				'status' => 'Error',
				'message' => 'Invoice not found'
			));
		}
	} else {
		echo json_encode(array(
			'status' => 'Error',
			'message' => 'Database error: ' . $mysqli->error
		));
	}

	$mysqli->close();
	exit;
}

?>