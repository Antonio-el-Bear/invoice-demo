/*******************************************************************************
* Simplified PHP Invoice System                                                *
*                                                                              *
* Version: 1.1.1	                                                               *
* Author:  James Brandon                                    				   *
*******************************************************************************/

$(document).ready(function() {
	var autoFillMatches = [];

	// Invoice Type
	$('#invoice_type').change(function() {
		var invoiceType = $("#invoice_type option:selected").text();
		$(".invoice_type").text(invoiceType);
	});

	// Load dataTables (only if library is available and element exists)
	if ($.fn.dataTable && $("#data-table").length) {
		$("#data-table").dataTable();
	}

	// add product
	$("#action_add_product").click(function(e) {
		e.preventDefault();
	    actionAddProduct();
	});

	// password strength
	var options = {
        onLoad: function () {
            $('#messages').text('Start typing password');
        },
        onKeyUp: function (evt) {
            $(evt.target).pwstrength("outputErrorList");
        }
    };
    $('#password').pwstrength(options);

	// add user
	$("#action_add_user").click(function(e) {
		e.preventDefault();
	    actionAddUser();
	});

	// update customer
	$(document).on('click', "#action_update_user", function(e) {
		e.preventDefault();
		updateUser();
	});

	// delete user
	$(document).on('click', ".delete-user", function(e) {
        e.preventDefault();

        var userId = 'action=delete_user&delete='+ $(this).attr('data-user-id'); //build a post data structure
        var user = $(this);

	    $('#delete_user').modal({ backdrop: 'static', keyboard: false }).one('click', '#delete', function() {
			deleteUser(userId);
			$(user).closest('tr').remove();
        });
   	});

   	// delete customer
	$(document).on('click', ".delete-customer", function(e) {
        e.preventDefault();

        var userId = 'action=delete_customer&delete='+ $(this).attr('data-customer-id'); //build a post data structure
        var user = $(this);

	    $('#delete_customer').modal({ backdrop: 'static', keyboard: false }).one('click', '#delete', function() {
			deleteCustomer(userId);
			$(user).closest('tr').remove();
        });
   	});

	// update customer
	$(document).on('click', "#action_update_customer", function(e) {
		e.preventDefault();
		updateCustomer();
	});

	// update product
	$(document).on('click', "#action_update_product", function(e) {
		e.preventDefault();
		updateProduct();
	});

	// login form
	$(document).bind('keypress', function(e) {
		e.preventDefault;
		
        if(e.keyCode==13){
            $('#btn-login').trigger('click');
        }
    });

	$(document).on('click','#btn-login', function(e){
		e.preventDefault();
		actionLogin();
	});

	// download CSV
	$(document).on('click', ".download-csv", function(e) {
		e.preventDefault();

		var action = 'action=download_csv'; //build a post data structure
        downloadCSV(action);

	});

	// email invoice
	$(document).on('click', ".email-invoice", function(e) {
        e.preventDefault();

        var invoiceId = 'action=email_invoice&id='+$(this).attr('data-invoice-id')+'&email='+$(this).attr('data-email')+'&invoice_type='+$(this).attr('data-invoice-type')+'&custom_email='+$(this).attr('data-custom-email'); //build a post data structure
		emailInvoice(invoiceId);
   	});

	// delete invoice
	$(document).on('click', ".delete-invoice", function(e) {
        e.preventDefault();

        var invoiceId = 'action=delete_invoice&delete='+ $(this).attr('data-invoice-id'); //build a post data structure
        var invoice = $(this);

	    $('#delete_invoice').modal({ backdrop: 'static', keyboard: false }).one('click', '#delete', function() {
			deleteInvoice(invoiceId);
			$(invoice).closest('tr').remove();
        });
   	});

	// delete product
	$(document).on('click', ".delete-product", function(e) {
        e.preventDefault();

        var productId = 'action=delete_product&delete='+ $(this).attr('data-product-id'); //build a post data structure
        var product = $(this);

	    $('#confirm').modal({ backdrop: 'static', keyboard: false }).one('click', '#delete', function() {
			deleteProduct(productId);
			$(product).closest('tr').remove();
        });
   	});

	// create customer
	$("#action_create_customer").click(function(e) {
		e.preventDefault();
	    actionCreateCustomer();
	});

	// auto fill invoice from most recent customer data
	$(document).on('click', '#auto_fill_invoice', function(e) {
		e.preventDefault();
		autoFillInvoice();
	});

	$(document).on('click', '#save_invoice_customer', function(e) {
		e.preventDefault();
		saveInvoiceCustomerProfile();
	});

	$(document).on('click', '.auto-fill-match', function(e) {
		e.preventDefault();

		var matchIndex = parseInt($(this).attr('data-match-index'), 10);
		if (isNaN(matchIndex) || !autoFillMatches[matchIndex]) {
			return;
		}

		applyAutoFillData(autoFillMatches[matchIndex]);
		$('#auto_fill_matches').modal('hide');
		$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
		$("#response .message").html("<strong>Success</strong>: Auto fill applied for " + escapeHtml(autoFillMatches[matchIndex].display_name || 'selected client') + ".");
		$("html, body").animate({ scrollTop: $('#response').offset().top }, 600);
	});

	$(document).on('click', '.add-suggested-product', function(e) {
		e.preventDefault();

		var $button = $(this);
		addInvoiceItemRow({
			product: $button.attr('data-product-name') || '',
			price: $button.attr('data-product-price') || '',
			qty: 1,
			discount: ''
		});
	});

	$(document).on('click', '#seed_it_services_catalog', function(e) {
		e.preventDefault();
		seedItServiceCatalog();
	});

	$(document).on('click', ".item-select", function(e) {

   		e.preventDefault;

   		var product = $(this);

   		$('#insert').modal({ backdrop: 'static', keyboard: false }).one('click', '#selected', function(e) {

		    var itemText = $('#insert').find("option:selected").text();
		    var itemValue = $('#insert').find("option:selected").val();

		    $(product).closest('tr').find('.invoice_product').val(itemText);
		    $(product).closest('tr').find('.invoice_product_price').val(itemValue);

		    updateTotals('.calculate');
        	calculateTotal();

   		});

   		return false;

   	});

   	$(document).on('click', ".select-customer", function(e) {

   		e.preventDefault;

   		var customer = $(this);

   		$('#insert_customer').modal({ backdrop: 'static', keyboard: false });

   		return false;

   	});

   	$(document).on('click', ".customer-select", function(e) {

		    var customer_name = $(this).attr('data-customer-name');
		    var customer_email = $(this).attr('data-customer-email');
		    var customer_phone = $(this).attr('data-customer-phone');

		    var customer_address_1 = $(this).attr('data-customer-address-1');
		    var customer_address_2 = $(this).attr('data-customer-address-2');
		    var customer_town = $(this).attr('data-customer-town');
		    var customer_county = $(this).attr('data-customer-county');
		    var customer_postcode = $(this).attr('data-customer-postcode');

		    var customer_name_ship = $(this).attr('data-customer-name-ship');
		    var customer_address_1_ship = $(this).attr('data-customer-address-1-ship');
		    var customer_address_2_ship = $(this).attr('data-customer-address-2-ship');
		    var customer_town_ship = $(this).attr('data-customer-town-ship');
		    var customer_county_ship = $(this).attr('data-customer-county-ship');
		    var customer_postcode_ship = $(this).attr('data-customer-postcode-ship');

		    $('#customer_name').val(customer_name);
		    $('#customer_email').val(customer_email);
		    $('#customer_phone').val(customer_phone);

		    $('#customer_address_1').val(customer_address_1);
		    $('#customer_address_2').val(customer_address_2);
		    $('#customer_town').val(customer_town);
		    $('#customer_county').val(customer_county);
		    $('#customer_postcode').val(customer_postcode);


		    $('#customer_name_ship').val(customer_name_ship);
		    $('#customer_address_1_ship').val(customer_address_1_ship);
		    $('#customer_address_2_ship').val(customer_address_2_ship);
		    $('#customer_town_ship').val(customer_town_ship);
		    $('#customer_county_ship').val(customer_county_ship);
		    $('#customer_postcode_ship').val(customer_postcode_ship);

		    $('#insert_customer').modal('hide');

	});

	// create invoice
	$("#action_create_invoice").click(function(e) {
		e.preventDefault();
	    actionCreateInvoice();
	});

	// update invoice
	$(document).on('click', "#action_edit_invoice", function(e) {
		e.preventDefault();
		updateInvoice();
	});

	// enable date pickers for due date and invoice date
	var dateFormat = $(this).attr('data-vat-rate');
	$('#invoice_date, #invoice_due_date').datetimepicker({
		showClose: false,
		format: dateFormat
	});

	// copy customer details to shipping
    $('input.copy-input').on("input", function () {
        $('input#' + this.id + "_ship").val($(this).val());
    });
    
    // remove product row
    $('#invoice_table').on('click', ".delete-row", function(e) {
    	e.preventDefault();
       	$(this).closest('tr').remove();
        calculateTotal();
    });

    // add new product row on invoice
    var cloned = $('#invoice_table tr:last').clone();
    $(".add-row").click(function(e) {
        e.preventDefault();
		addInvoiceItemRow();
    });
    
    calculateTotal();
    
    $('#invoice_table').on('input', '.calculate', function () {
	    updateTotals(this);
	    calculateTotal();
	});

	$('#invoice_totals').on('input', '.calculate', function () {
	    calculateTotal();
	});

	$('#invoice_product').on('input', '.calculate', function () {
	    calculateTotal();
	});

	$('.remove_vat').on('change', function() {
        calculateTotal();
    });

	function updateTotals(elem) {

        var tr = $(elem).closest('tr'),
            quantity = $('[name="invoice_product_qty[]"]', tr).val(),
	        price = $('[name="invoice_product_price[]"]', tr).val(),
            isPercent = $('[name="invoice_product_discount[]"]', tr).val().indexOf('%') > -1,
            percent = $.trim($('[name="invoice_product_discount[]"]', tr).val().replace('%', '')),
	        subtotal = parseInt(quantity) * parseFloat(price);

        if(percent && $.isNumeric(percent) && percent !== 0) {
            if(isPercent){
                subtotal = subtotal - ((parseFloat(percent) / 100) * subtotal);
            } else {
                subtotal = subtotal - parseFloat(percent);
            }
        } else {
            $('[name="invoice_product_discount[]"]', tr).val('');
        }

	    $('.calculate-sub', tr).val(subtotal.toFixed(2));
	}

	function calculateTotal() {
	    
	    var grandTotal = 0,
	    	disc = 0,
	    	c_ship = parseInt($('.calculate.shipping').val()) || 0;

	    $('#invoice_table tbody tr').each(function() {
            var c_sbt = $('.calculate-sub', this).val(),
                quantity = $('[name="invoice_product_qty[]"]', this).val(),
	            price = $('[name="invoice_product_price[]"]', this).val() || 0,
                subtotal = parseInt(quantity) * parseFloat(price);
            
            grandTotal += parseFloat(c_sbt);
            disc += subtotal - parseFloat(c_sbt);
	    });

        // VAT, DISCOUNT, SHIPPING, TOTAL, SUBTOTAL:
	    var subT = parseFloat(grandTotal),
	    	finalTotal = parseFloat(grandTotal + c_ship),
	    	vat = parseInt($('.invoice-vat').attr('data-vat-rate'));

	    $('.invoice-sub-total').text(subT.toFixed(2));
	    $('#invoice_subtotal').val(subT.toFixed(2));
        $('.invoice-discount').text(disc.toFixed(2));
        $('#invoice_discount').val(disc.toFixed(2));

        if($('.invoice-vat').attr('data-enable-vat') === '1') {

	        if($('.invoice-vat').attr('data-vat-method') === '1') {
		        $('.invoice-vat').text(((vat / 100) * finalTotal).toFixed(2));
		        $('#invoice_vat').val(((vat / 100) * finalTotal).toFixed(2));
	            $('.invoice-total').text((finalTotal).toFixed(2));
	            $('#invoice_total').val((finalTotal).toFixed(2));
	        } else {
	            $('.invoice-vat').text(((vat / 100) * finalTotal).toFixed(2));
	            $('#invoice_vat').val(((vat / 100) * finalTotal).toFixed(2));
		        $('.invoice-total').text((finalTotal + ((vat / 100) * finalTotal)).toFixed(2));
		        $('#invoice_total').val((finalTotal + ((vat / 100) * finalTotal)).toFixed(2));
	        }
		} else {
			$('.invoice-total').text((finalTotal).toFixed(2));
			$('#invoice_total').val((finalTotal).toFixed(2));
		}

		// remove vat
    	if($('input.remove_vat').is(':checked')) {
	        $('.invoice-vat').text("0.00");
	        $('#invoice_vat').val("0.00");
            $('.invoice-total').text((finalTotal).toFixed(2));
            $('#invoice_total').val((finalTotal).toFixed(2));
	    }

	}

	function actionAddUser() {

		var errorCounter = validateForm();

		if (errorCounter > 0) {
		    $("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
		    $("#response .message").html("<strong>Error</strong>: It appear's you have forgotten to complete something!");
		    $("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
		} else {

			$(".required").parent().removeClass("has-error");

			var $btn = $("#action_add_user").button("loading");

			$.ajax({

				url: 'response.php',
				type: 'POST',
				data: $("#add_user").serialize(),
				dataType: 'json',
				success: function(data){
					$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
					$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
					$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
					$btn.button("reset");
				},
				error: function(data){
					$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
					$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
					$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
					$btn.button("reset");
				}

			});
		}

	}

	function actionAddProduct() {

		var errorCounter = validateForm();

		if (errorCounter > 0) {
		    $("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
		    $("#response .message").html("<strong>Error</strong>: It appear's you have forgotten to complete something!");
		    $("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
		} else {

			$(".required").parent().removeClass("has-error");

			var $btn = $("#action_add_product").button("loading");

			$.ajax({

				url: 'response.php',
				type: 'POST',
				data: $("#add_product").serialize(),
				dataType: 'json',
				success: function(data){
					$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
					$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
					$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
					$btn.button("reset");
				},
				error: function(data){
					$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
					$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
					$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
					$btn.button("reset");
				}

			});
		}

	}

	function actionCreateCustomer(){

		var errorCounter = validateForm();

		if (errorCounter > 0) {
		    $("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
		    $("#response .message").html("<strong>Error</strong>: It appear's you have forgotten to complete something!");
		    $("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
		} else {

			var $btn = $("#action_create_customer").button("loading");

			$(".required").parent().removeClass("has-error");

			$.ajax({

				url: 'response.php',
				type: 'POST',
				data: $("#create_customer").serialize(),
				dataType: 'json',
				success: function(data){
					$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
					$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
					$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
					$("#create_customer").before().html("<a href='./customer-add.php' class='btn btn-primary'>Add New Customer</a>");
					$("#create_cuatomer").remove();
					$btn.button("reset");
				},
				error: function(data){
					$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
					$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
					$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
					$btn.button("reset");
				} 

			});
		}

	}

	function actionCreateInvoice(){

		var errorCounter = validateForm();

		if (errorCounter > 0) {
		    $("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
		    $("#response .message").html("<strong>Error</strong>: It appear's you have forgotten to complete something!");
		    $("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
		} else {

			var $btn = $("#action_create_invoice").button("loading");

			$(".required").parent().removeClass("has-error");
			$("#create_invoice").find(':input:disabled').removeAttr('disabled');

			$.ajax({

				url: 'response.php',
				type: 'POST',
				data: $("#create_invoice").serialize(),
				dataType: 'json',
				success: function(data){
					$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
					$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
					$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
					$("#create_invoice").before().html("<a href='../invoice-add.php' class='btn btn-primary'>Create new invoice</a>");
					$("#create_invoice").remove();
					$btn.button("reset");
				},
				error: function(data){
					$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
					$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
					$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
					$btn.button("reset");
				} 

			});
		}

	}

	function saveInvoiceCustomerProfile() {
		var customerName = $.trim($("#customer_name").val());
		var customerEmail = $.trim($("#customer_email").val());

		if (customerName === '' || customerEmail === '') {
			$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
			$("#response .message").html("<strong>Error</strong>: Enter customer name and email before saving the client profile.");
			$("#customer_name, #customer_email").parent().addClass("has-error");
			$("html, body").animate({ scrollTop: $('#response').offset().top }, 600);
			return;
		}

		$("#customer_name, #customer_email").parent().removeClass("has-error");

		var $btn = $("#save_invoice_customer").button("loading");
		var payload = $("#create_invoice").serializeArray();
		payload.push({ name: 'action', value: 'save_invoice_customer_profile' });

		$.ajax({
			url: 'response.php',
			type: 'POST',
			dataType: 'json',
			data: $.param(payload),
			success: function(data) {
				$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
				$("#response").removeClass("alert-warning").addClass(data.status === 'Success' ? 'alert-success' : 'alert-warning').fadeIn();
				$("html, body").animate({ scrollTop: $('#response').offset().top }, 600);
				if (data.status === 'Success') {
					autoFillInvoice({
						successMessage: 'Client profile saved and suggestions refreshed.',
						noMatchMessage: 'Client profile saved, but no autofill match could be refreshed yet.'
					});
				}
				$btn.button("reset");
			},
			error: function() {
				$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
				$("#response .message").html("<strong>Error</strong>: Unable to save the client profile.");
				$btn.button("reset");
			}
		});
	}

	function autoFillInvoice(options) {
		var settings = options || {};
		var customerEmail = $.trim($("#customer_email").val());
		var customerName = $.trim($("#customer_name").val());

		if (customerEmail === "" && customerName === "") {
			$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
			$("#response .message").html("<strong>Error</strong>: Enter customer email or name, then click Auto Fill Agent.");
			$("#customer_email, #customer_name").parent().addClass("has-error");
			$("html, body").animate({ scrollTop: $('#response').offset().top }, 600);
			return;
		}

		$("#customer_email, #customer_name").parent().removeClass("has-error");

		var $btn = $("#auto_fill_invoice").button("loading");

		$.ajax({
			url: 'response.php',
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'auto_fill_invoice',
				customer_email: customerEmail,
				customer_name: customerName
			},
			success: function(data) {
				if (data.status !== 'Success') {
					$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
					$("#response .message").html("<strong>Notice</strong>: " + data.message);
					$btn.button("reset");
					return;
				}

				autoFillMatches = (data.data && data.data.matches) ? data.data.matches : [];

				if (autoFillMatches.length === 0) {
					$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
					$("#response .message").html("<strong>Notice</strong>: " + (settings.noMatchMessage || "No matching customer record found for auto fill."));
					$btn.button("reset");
					return;
				}

				if (autoFillMatches.length === 1) {
					applyAutoFillData(autoFillMatches[0]);
					$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
					$("#response .message").html("<strong>Success</strong>: " + (settings.successMessage || "Auto fill applied from the best matching profile."));
					$("html, body").animate({ scrollTop: $('#response').offset().top }, 600);
				} else {
					renderAutoFillMatches(autoFillMatches);
					$('#auto_fill_matches').modal({ backdrop: 'static', keyboard: false });
					$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
					$("#response .message").html("<strong>Choose</strong>: " + data.message);
				}

				$btn.button("reset");
			},
			error: function() {
				$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
				$("#response .message").html("<strong>Error</strong>: Auto Fill Agent failed. Please try again.");
				$btn.button("reset");
			}
		});
	}

	function renderAutoFillMatches(matches) {
		var html = '';

		$.each(matches, function(index, match) {
			var invoiceInfo = match.invoice_number ? 'Last invoice #' + escapeHtml(match.invoice_number) : 'No previous invoice items';
			var locationInfo = match.display_location ? '<div>' + escapeHtml(match.display_location) + '</div>' : '';
			var emailInfo = match.display_email ? '<div>' + escapeHtml(match.display_email) + '</div>' : '<div>No email saved</div>';
			var scoreInfo = typeof match.match_score !== 'undefined' ? '<div>Match score: ' + escapeHtml(String(match.match_score)) + '</div>' : '';

			html += '' +
				'<a href="#" class="list-group-item auto-fill-match" data-match-index="' + index + '">' +
					'<h4 class="list-group-item-heading">' + escapeHtml(match.display_name || 'Unnamed client') + '</h4>' +
					'<p class="list-group-item-text">' +
						'<strong>' + escapeHtml(match.source_label || 'Saved profile') + '</strong><br>' +
						emailInfo +
						locationInfo +
						scoreInfo +
						'<div>' + invoiceInfo + '</div>' +
					'</p>' +
				'</a>';
		});

		$('#auto_fill_match_list').html(html);
	}

	function applyAutoFillData(payload) {
		if (!payload || !payload.customer) {
			return;
		}

		var customer = payload.customer;

		$("#customer_name").val(customer.name || "");
		$("#customer_email").val(customer.email || "");
		$("#customer_phone").val(customer.phone || "");
		$("#customer_address_1").val(customer.address_1 || "");
		$("#customer_address_2").val(customer.address_2 || "");
		$("#customer_town").val(customer.town || "");
		$("#customer_county").val(customer.county || "");
		$("#customer_postcode").val(customer.postcode || "");

		$("#customer_name_ship").val(customer.name_ship || customer.name || "");
		$("#customer_address_1_ship").val(customer.address_1_ship || customer.address_1 || "");
		$("#customer_address_2_ship").val(customer.address_2_ship || customer.address_2 || "");
		$("#customer_town_ship").val(customer.town_ship || customer.town || "");
		$("#customer_county_ship").val(customer.county_ship || customer.county || "");
		$("#customer_postcode_ship").val(customer.postcode_ship || customer.postcode || "");

		if (payload.items && payload.items.length > 0) {
			var $tbody = $('#invoice_table tbody');
			$tbody.empty();

			$.each(payload.items, function(_, item) {
				addInvoiceItemRow({
					product: item.product || '',
					qty: item.qty || 1,
					price: item.price || '',
					discount: item.discount || '',
					subtotal: item.subtotal || '0.00'
				});
			});
		} else {
			ensureSingleEmptyInvoiceRow();
		}

		renderSuggestedProducts(payload.suggestions || []);
		calculateTotal();
	}

	function renderSuggestedProducts(suggestions) {
		var html = '';

		if (!suggestions || suggestions.length === 0) {
			$('#suggested_products_list').empty();
			$('#suggested_products_panel').hide();
			return;
		}

		$.each(suggestions, function(_, suggestion) {
			html += '' +
				'<div class="col-sm-6 col-md-4">' +
					'<div class="panel panel-info">' +
						'<div class="panel-heading"><strong>' + escapeHtml(suggestion.product_name || 'Product') + '</strong></div>' +
						'<div class="panel-body">' +
							'<p>' + escapeHtml(suggestion.product_desc || 'No description saved') + '</p>' +
							'<p><strong>Price:</strong> ' + escapeHtml(String(suggestion.product_price || '0.00')) + '</p>' +
							'<p><strong>Source:</strong> ' + escapeHtml(suggestion.source_label || 'Suggestion') + '</p>' +
							'<a href="#" class="btn btn-info btn-xs add-suggested-product" data-product-name="' + escapeHtml(suggestion.product_name || '') + '" data-product-price="' + escapeHtml(String(suggestion.product_price || '')) + '">Add To Invoice</a>' +
						'</div>' +
					'</div>' +
				'</div>';
		});

		$('#suggested_products_list').html(html);
		$('#suggested_products_panel').show();
	}

	function seedItServiceCatalog() {
		var $btn = $('#seed_it_services_catalog').button('loading');

		$.ajax({
			url: 'response.php',
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'seed_it_service_products'
			},
			success: function(data) {
				if (data.status !== 'Success') {
					$('#response').removeClass('alert-success').addClass('alert-warning').fadeIn();
					$('#response .message').html('<strong>Error</strong>: ' + (data.message || 'Unable to add IT service templates.'));
					$btn.button('reset');
					return;
				}

				var addedCount = data.data && typeof data.data.added !== 'undefined' ? data.data.added : 0;
				var skippedCount = data.data && typeof data.data.skipped !== 'undefined' ? data.data.skipped : 0;
				var templates = data.data && data.data.templates ? data.data.templates : [];

				renderSuggestedProducts(templates);
				$('#response').removeClass('alert-warning').addClass('alert-success').fadeIn();
				$('#response .message').html('<strong>Success</strong>: IT service templates processed. Added ' + escapeHtml(String(addedCount)) + ', skipped ' + escapeHtml(String(skippedCount)) + '.');
				$('html, body').animate({ scrollTop: $('#response').offset().top }, 600);
				$btn.button('reset');
			},
			error: function() {
				$('#response').removeClass('alert-success').addClass('alert-warning').fadeIn();
				$('#response .message').html('<strong>Error</strong>: Failed to add IT service templates to the catalog.');
				$btn.button('reset');
			}
		});
	}

	function ensureSingleEmptyInvoiceRow() {
		var $tbody = $('#invoice_table tbody');
		if ($tbody.find('tr').length === 0) {
			addInvoiceItemRow();
		}
	}

	function addInvoiceItemRow(itemData) {
		var data = itemData || {};
		var $row = cloned.clone();

		$row.find('.invoice_product').val(data.product || '');
		$row.find('[name="invoice_product_qty[]"]').val(data.qty || 1);
		$row.find('[name="invoice_product_price[]"]').val(data.price || '');
		$row.find('[name="invoice_product_discount[]"]').val(data.discount || '');
		$row.find('[name="invoice_product_sub[]"]').val(data.subtotal || '0.00');
		$('#invoice_table tbody').append($row);
		updateTotals($row.find('.invoice_product_price'));
		calculateTotal();
		return $row;
	}

	function escapeHtml(value) {
		return $('<div>').text(value || '').html();
	}

   	function deleteProduct(productId) {

        jQuery.ajax({

        	url: 'response.php',
            type: 'POST', 
            data: productId,
            dataType: 'json', 
            success: function(data){
				$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
				$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
				$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
				$btn.button("reset");
			},
			error: function(data){
				$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
				$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
				$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
				$btn.button("reset");
			} 
    	});

   	}

   	function deleteUser(userId) {

        jQuery.ajax({

        	url: 'response.php',
            type: 'POST', 
            data: userId,
            dataType: 'json', 
            success: function(data){
				$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
				$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
				$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
				$btn.button("reset");
			},
			error: function(data){
				$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
				$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
				$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
				$btn.button("reset");
			} 
    	});

   	}

	function deleteCustomer(userId) {

        jQuery.ajax({

        	url: 'response.php',
            type: 'POST', 
            data: userId,
            dataType: 'json', 
            success: function(data){
				$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
				$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
				$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
			},
			error: function(data){
				$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
				$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
				$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
			} 
    	});

   	}

   	function emailInvoice(invoiceId) {

        jQuery.ajax({

        	url: 'response.php',
            type: 'POST', 
            data: invoiceId,
            dataType: 'json', 
            success: function(data){
				$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
				$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
				$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
			},
			error: function(data){
				$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
				$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
				$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
			} 
    	});

   	}

   	function deleteInvoice(invoiceId) {

        jQuery.ajax({

        	url: 'response.php',
            type: 'POST', 
            data: invoiceId,
            dataType: 'json', 
            success: function(data){
				$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
				$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
				$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
				$btn.button("reset");
			},
			error: function(data){
				$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
				$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
				$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
				$btn.button("reset");
			} 
    	});

   	}

   	function updateProduct() {

   		var $btn = $("#action_update_product").button("loading");

        jQuery.ajax({

        	url: 'response.php',
            type: 'POST', 
            data: $("#update_product").serialize(),
            dataType: 'json', 
            success: function(data){
				$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
				$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
				$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
				$btn.button("reset");
			},
			error: function(data){
				$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
				$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
				$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
				$btn.button("reset");
			} 
    	});

   	}

   	function updateUser() {

   		var $btn = $("#action_update_user").button("loading");

        jQuery.ajax({

        	url: 'response.php',
            type: 'POST', 
            data: $("#update_user").serialize(),
            dataType: 'json', 
            success: function(data){
				$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
				$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
				$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
				$btn.button("reset");
			},
			error: function(data){
				$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
				$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
				$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
				$btn.button("reset");
			} 
    	});

   	}

   	function updateCustomer() {

   		var $btn = $("#action_update_customer").button("loading");

        jQuery.ajax({

        	url: 'response.php',
            type: 'POST', 
            data: $("#update_customer").serialize(),
            dataType: 'json', 
            success: function(data){
				$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
				$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
				$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
				$btn.button("reset");
			},
			error: function(data){
				$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
				$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
				$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
				$btn.button("reset");
			} 
    	});

   	}

   	function updateInvoice() {

   		var $btn = $("#action_update_invoice").button("loading");
   		$("#update_invoice").find(':input:disabled').removeAttr('disabled');

        jQuery.ajax({

        	url: 'response.php',
            type: 'POST', 
            data: $("#update_invoice").serialize(),
            dataType: 'json', 
            success: function(data){
				$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
				$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
				$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
				$btn.button("reset");
			},
			error: function(data){
				$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
				$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
				$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
				$btn.button("reset");
			} 
    	});

   	}

   	function downloadCSV(action) {

   		jQuery.ajax({

   			url: 'response.php',
   			type: 'POST',
   			data: action,
   			dataType: 'json',
   			success: function(data){
				$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
				$("#response").removeClass("alert-warning").addClass("alert-success").fadeIn();
				$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);

				// Auto-trigger browser download if URL is provided
				if (data.download_url) {
					var link = document.createElement('a');
					link.href = data.download_url;
					link.download = data.file_name || '';
					document.body.appendChild(link);
					link.click();
					document.body.removeChild(link);
				}
			},
			error: function(data){
				$("#response .message").html("<strong>" + data.status + "</strong>: " + data.message);
				$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
				$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
			} 
   		});

   	}

   	// login function
	function actionLogin() {

		var errorCounter = validateForm();

		if (errorCounter > 0) {

		    $("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
		    $("#response .message").html("<strong>Error</strong>: Missing something are we? check and try again!");
		    $("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);

		} else {

			var $btn = $("#btn-login");
			var originalText = $btn.text();
			
			// Disable button and show loading state
			$btn.prop("disabled", true).text("Loading...");

			jQuery.ajax({
				url: 'login.php',
				type: "POST",
				data: $("#login_form, #login-form").first().serialize(),
				success: function(response){
					if(response == 1) {
						window.location = "dashboard.php";
					} else {
						$("#response .message").html("<strong>Error</strong>: Invalid username or password.");
						$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
						$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
					}
					$btn.prop("disabled", false).text(originalText);
				},
				error: function(xhr, status, error){
					$("#response .message").html("<strong>Error</strong>: Failed to connect to server. Check console for details.");
					$("#response").removeClass("alert-success").addClass("alert-warning").fadeIn();
					$("html, body").animate({ scrollTop: $('#response').offset().top }, 1000);
					$btn.prop("disabled", false).text(originalText);
				}
			});

		}
		
	}

   	function validateForm() {
	    // error handling
	    var errorCounter = 0;

	    $(".required").each(function(i, obj) {

	        if($(this).val() === ''){
	            $(this).parent().addClass("has-error");
	            errorCounter++;
	        } else{ 
	            $(this).parent().removeClass("has-error"); 
	        }


	    });

	    return errorCounter;
	}

});