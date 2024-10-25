@extends('admin.layouts.app')
@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">

    <!-- Include jQuery and Datatables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>

    <!-- Include Select2 CSS and JS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"
        integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        #open-modal-btn {
            display: none;
        }

        #settings-modal {
            display: none;
        }

        .select2 {
            width: 100% !important;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid card ">
        <div class ="col-md-12 card-body">
            <div class="row justify-content-center">
                <div class="col-12 mb-2 text-right">
                    <button class="btn btn-info" onclick="hideShowData()" id="open-modal-btn" state="0">Push to CRM -
                        <span class="total_p_count">0</span></button>
                </div>
                <!-- Modal for additional settings -->
                <div id="settings-modal" class="form-control mb-4">
                    <h2>Push to CRM Setting</h2>
                    <form id="settings-form">
                        <div class="form-row">
                            @include('layouts.discount')
                            <div class="col-md-2">
                                <a class="btn btn-secondary mt-2" onclick="updatePricesTable()">Recalculate</a>
                            </div>
                            <div style="max-height:400px;display: block;overflow-y: auto;">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>SKU</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>CRM Price</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="selected-products-container"></tbody>
                                </table>
                                <div>
                                </div>
                            </div>
                            <div class="form-group col-md-12">
                                <button type="submit" class="btn push_to_crmbtn btn-primary mt-2">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" class="form-control">
                                <option value="">Select a category</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6 ">
                        <div class="form-group">
                            <label for="search">Search by Keywords</label>
                            <input type="text" id="search" name="search" class="form-control"
                                placeholder="Enter keywords">
                        </div>
                    </div>

                    <div class="col-md-6 ">
                        <div class="form-group">
                            <label for="search">Search by SKU</label>
                            <input type="text" id="sku" name="sku" class="form-control"
                                placeholder="Enter SKU">
                        </div>
                    </div>

                    <div class="col-md-6 mt-4">
                        <div class="form-group">
                            <label>AU Free Shipping</label>
                            <select class="form-select" name="au_free_shipping">
                                <option value="" selected>Both</option>
                                <option value="true">Yes</option>
                                <option value="false">No</option>
                            </select>

                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>New Arrival</label>
                            <select class="form-select" name="new_arrival">
                                <option value="">Both</option>
                                <option value="true" selected>Yes</option>
                                <option value="false">No</option>
                            </select>

                        </div>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-12 ">
                        <button class="btn btn-secondary mt-2 float-end" onclick="fetchLatestProducts()">Fetch new products</button>
                    </div>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-1 ">
                    <input type="checkbox" id="product_selection_all" class="form-check-input product_selection_all "
                        onchange="dataCheckboxAll()"><label class="form-check-label mr-2" for="product_selection_all"
                        id="label_select_all"> Select
                        All</label>
                </div>
            </div>
            <table id="products-table" class="table table-striped w-full w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Image</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>SKU</th>
                        <th>New Arrival</th>
                        <th>Free Shipping</th>
                        <th>CRM Status</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

        </div>
    </div>
    </div>
    @include('layouts.script')
    <script>
        let table = null;
        let selectedProducts = {};

        let pagesRecord = {};
        let lastRecordsKey = 'last_totals';
        let totalRecords = localStorage.getItem(lastRecordsKey) ?? 0;
        let latestProducts = 0;

        function fetchLatestProducts() {
            latestProducts = 1;
            table.draw();
            setTimeout(function() {
                latestProducts = 0;

            }, 500);
        }
        function safeBtoa(str) {
            return btoa(unescape(encodeURIComponent(str)));
        }
        function appendRow(productData) {
            try {
                let uniqueId = productData.sku;
                if ($('#selected_sku_' + uniqueId).length > 0) {
                    return;
                }
                let price = productData.RrpPrice ?? 0;
                productData.crm_price = addPercentageToPrice(price);
                let productDataBase64 = safeBtoa(JSON.stringify(productData));
                $('#selected-products-container').append(
                    `
                    <tr id="selected_sku_${uniqueId}" >
                        <input type="hidden" class="form-control" name="selected_products[]" value='${productDataBase64}' >
                        <td>${productData.title}</td>
                        <td>${productData.sku}</td>
                        <td>${productData.Category??''}</td>
                        <td>${price}</td>
                        <td id="crm_price_${productData.sku}" class="crmPriceData" data-price="${price}">${productData.crm_price}</td>
                        <td><span style="margin-left:13px;" onclick="dataUnchecked('${uniqueId}')"><i class="fa fa-trash text-danger ml-3"></i></span></td>
                    </tr>
                    `
                );
            } catch (error) {
                console.log(error);
            }

        }

        function addPercentageToPrice(price) {

            price = parseFloat(price);
            let discountType = $('#discount_type').val();
            let percentage = parseFloat((discountType === 'flat' ? $('#flat_discount').val() : $(
                '#percentage_discount').val()));
            let finalPrice = price;
            try {
                if (discountType == 'percentage') {
                    percentage = (price * percentage) / 100;
                }

                finalPrice += percentage;
            } catch (error) {
                console.log(finalPrice);
            }

            if (finalPrice.toString().includes('.')) {
                //finalPrice.toFixed(1) + ` ~ ` +
                finalPrice = Math.ceil(finalPrice.toString());
            }
            return finalPrice;
        }

        function hideShowData() {
            let modalbtn = $('#open-modal-btn');
            let previousState = parseInt(modalbtn.attr('state'));
            modalbtn.attr('state', previousState == 1 ? 0 : 1);
            let settingModal = $('#settings-modal');
            if (previousState == 0) {
                settingModal.show()
            } else {
                settingModal.hide();
            }


        }

        function appendProduct() {
            // $('#selected-products-container').empty();
            // $('#product_heading').empty();

            // $.each(selectedProducts, function(sku, productData) {

            // });

            // // Hide the containers if there are no products
            // if (Object.keys(selectedProducts).length === 0) {
            //     $('#product_heading').hide();
            //     $('#selected-products-container').hide();
            // } else {
            //     $('#product_heading').show();
            //     $('#selected-products-container').show();
            // }
            updateUI();
        }

        function dataUnchecked(sku) {
            delete selectedProducts[sku];
            $(`#selected_sku_${sku}`).remove();
            $(`[data-row-id="product_${sku}"]`).prop('checked', false);
        }

        function updateUI() {
            let selectedProductsLength = Object.keys(selectedProducts).length;
            // Update button visibility
            if (selectedProductsLength > 0) {
                $('#open-modal-btn').show();
                //$('#settings-modal').hide();
            } else {
                //$('#open-modal-btn').hide();
                //$('#settings-modal').hide();
            }

            // Update total count
            $('.total_p_count').text(selectedProductsLength);
        }

        function dataCheckboxAll() {
            let allData = $('.product_selection_all');
            let isChecked = allData.prop('checked');
            let label = (isChecked ? 'Deselect ' : 'Select') + ' All';
            $('#label_select_all').text(label);
            $('.product_selection').prop('checked', isChecked).trigger('change');
        }




        function dataCheckbox(json, sku) {
            let elem = $(`[data-row-id="product_${sku}"]`);
            if (!elem.prop('checked')) {
                dataUnchecked(sku);
            } else {
                let product = JSON.parse(atob(json));
                selectedProducts[sku] = product;
                appendRow(product);
            }

            let selectedProductsLength = Object.keys(selectedProducts).length;
            if (selectedProductsLength > 0) {
                $('#open-modal-btn').show();
                //$('#settings-modal').hide();
            } else {
                // $('#open-modal-btn').hide();
                // $('#settings-modal').hide();
            }
            $('.total_p_count').text(selectedProductsLength);
        }

        function updatePricesTable() {

            document.querySelectorAll('.crmPriceData').forEach(x => {
                x.innerText = addPercentageToPrice(x.getAttribute('data-price'));
            });

        }

        function chunkArray(array, chunkSize) {
            const result = [];
            for (let i = 0; i < array.length; i += chunkSize) {
                const chunk = array.slice(i, i + chunkSize);
                result.push(chunk);
            }
            return result;
        }

        $(document).ready(function() {

            //click //dblclick
            $('body').on('click', '#products-table tbody td', function() {
                let elem = $(this);
                let pr = '.product_selection';
                let chkbox = elem.find(pr);

                if (chkbox.length == 1) {
                    return;
                } else {
                    let tr = elem.closest('tr');
                    chkbox = tr.find(pr);
                    let lastBox = chkbox.prop('checked');
                    chkbox.prop('checked', !lastBox);
                    chkbox.trigger('change');
                }

            });
            $('#category').select2({
                placeholder: 'Select a category',
                allowClear: true,
                ajax: {
                    url: '{{ route('categories.index') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term,
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.data.categories.map(function(category) {
                                return {
                                    id: category.category_id,
                                    text: category.title
                                };
                            }),
                            pagination: {
                                more: data.data.current_page < data.data.total_pages
                            }
                        };
                    },
                    cache: true
                }
            });

            table = $('#products-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                "searching": false,
                pageLength: 100,
                lengthChange: false,
                ajax: {
                    url: '{{ route('products.get') }}',
                    method: 'POST',
                    data: function(d) {
                        d._token = '{{ csrf_token() }}';
                        d.category = $('#category').val();
                        d.search = $('#search').val();
                        d.sku = $('#sku').val();
                        d.hard_refresh = latestProducts;
                        d.totalRecords = totalRecords;
                        d.au_free_shipping = $('[name="au_free_shipping"]').val();
                        d.new_arrival = $('[name="new_arrival"]').val();
                        return d;
                    },
                    error: function(request, status, error) {
                        console.log(request.responseText);
                    }
                },
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {

                            let isAlready = selectedProducts[row.sku] ?? false;
                            if (isAlready) {
                                data = data.replace('checkdataselectedmethod', 'checked');
                                $(data).trigger('change');
                            }
                            return data;
                        }
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'image',
                        name: 'image'
                    },
                    {
                        data: 'price',
                        name: 'price'
                    },
                    {
                        data: 'desc',
                        name: 'desc'
                    },
                    {
                        data: 'Category',
                        name: 'Category'
                    },
                    {
                        data: 'sku',
                        name: 'sku'
                    },
                    {
                        data: 'is_new_arrival',
                        name: 'is_new_arrival'
                    },
                    {
                        data: 'freeshipping',
                        name: 'freeshipping'
                    },
                    {
                        data: 'sku',
                        name: 'sku',
                        render: function(data, type, row) {
                            return '<span class="status" data-sku="' + data +
                                '">No</span>';
                        }
                    }
                ]
            });

            function applyFilters() {
                table.draw();
            }


            $('#settings-form').submit(function(event) {
                event.preventDefault();

                let products = [];
                let btn = document.querySelector('.push_to_crmbtn');

                let discountType = $('#discount_type').val();
                let discountValue = (discountType === 'flat' ? $('#flat_discount').val() : $(
                    '#percentage_discount').val()) + '|' + discountType;
                if (isNaN(discountValue)) {
                    discountValue = 0;
                }
                discountValue = discountValue + '|' + discountType;

                $('#selected-products-container input').each(function() {
                    let productDataBase64 = $(this).val();
                    try {
                        let productDataJson = atob(productDataBase64);

                        products.push(productDataJson);
                    } catch (error) {

                    }
                });

                if (Object.keys(products).length === 0) {
                    alert("Please select at least one product.");
                    return;
                }
                btn.disabled = true;
                btn.innerText = 'Please wait...';
                const chunkedArray = chunkArray(products, 10);

                function processProducts(rows, total, index = 0, token = null, msg = 'Processed') {

                    if (index == total) {
                        return;
                    }
                    let formData = new FormData();
                    let chunks = rows[index];
                    formData.append('extra_percentage', discountValue);
                    formData.append('_token', '{{ csrf_token() }}');
                    chunks.forEach(x => {
                        formData.append('selected_products[]', x);
                    })
                    $.ajax({
                        url: '/api/push-to-crm',
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {

                            if (index == total - 1) {

                                $('.product_selection').prop('checked', false).trigger(
                                    'change');
                                toastr.success(msg, {
                                    timeOut: 10000
                                });
                                $('#selected-products-container').empty();
                                //alert('Data pushed to CRM successfully!');
                                $('#settings-modal').hide();
                                btn.disabled = false;
                                btn.innerText = 'Submit';
                                updateSKUStatus();
                            }

                        },
                        error: function(response) {
                            toastr.error('Failed to push data to CRM.', {
                                timeOut: 10000
                            });
                            btn.disabled = false;
                            btn.innerText = 'Submit';

                        }
                    });
                    setTimeout(function() {
                        processProducts(rows, total, index + 1, null);
                    }, 3500);
                }
                processProducts(chunkedArray, chunkedArray.length, 0);



                //formData.append('selected_products', JSON.stringify(products));




            });



            // Submit selected products to CRM
            // $('#settings-form').submit(function(event) {
            //     event.preventDefault();
            //      var formData =[];
            //     $('#selected-products-container input').each(function() {
            //       formData.append($(this).attr('name'), $(this).val());
            //      });

            //     if (selectedProducts.length === 0) {
            //         alert("Please select at least one product.");
            //         return;
            //     }

            //     let discountValue = null;
            //     let discountType = $('#discount_type').val();
            //     if (discountType === 'flat') {
            //         discountValue = $('#flat_discount').val() + '|' + discountType;
            //     } else if (discountType === 'percentage') {
            //         discountValue = $('#percentage_discount').val() + '|' + discountType;
            //     }
            //     console.log(selectedProducts);
            //     console.log(discountValue);
            //     $.ajax({
            //         url: '/api/push-to-crm',
            //         method: 'POST',
            //         data: {
            //             formData : formData
            //             extra_percentage: discountValue,
            //             _token: '{{ csrf_token() }}'
            //         },
            //         success: function(response) {
            //             alert('Data pushed to CRM successfully!');
            //             $('#settings-modal').hide();
            //         },
            //         error: function(response) {
            //             console.log(response.responseJSON); //
            //             alert('Failed to push data to CRM.');
            //         }
            //     });
            // });

            // Refresh the products table when the category changes
            $('#category').change(applyFilters);
            $('#search').on('input', applyFilters);
            $('[name="au_free_shipping"], [name="new_arrival"]').change(applyFilters);

            table.on('xhr', function(e, settings, json, xhr) {

                // Actions after AJAX request completes
                totalRecords = json.recordsTotal;
                localStorage.setItem(lastRecordsKey, totalRecords);

                setTimeout(function() {
                    let xt = $('.product_selection_all');
                    if (xt.prop('checked')) {
                        xt.trigger('change');
                    }
                    updateSKUStatus();
                }, 800);
            });
        });


        function updateSKUStatus() {
            var skus = [];
            $('.status').each(function() {
                var sku = $(this).data('sku');
                skus.push(sku);
            });

            $.ajax({
                url: '/api/update-sku-status',
                method: 'POST',
                data: {
                    skus: skus,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    for (let [k, v] of Object.entries(response.data)) {
                        var statusElement = $('span.status[data-sku="' + k + '"]');
                        statusElement.text(v.trim().toString() != '' ? 'Yes' : 'No');
                    }
                },
                error: function(xhr) {
                    console.error('Failed to update SKU status:', xhr.responseText);
                }
            });
        }
    </script>
@endsection
