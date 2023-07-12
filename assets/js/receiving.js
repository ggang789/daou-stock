const COMMON = {
    isEmpty: function(val) {
        val = typeof val == 'string' ? val.trim() : val;

        return val === ''
            || val == null
            || (typeof val == 'object' && !Object.keys(val).length);
    }
};

const PRODUCT_RECEIVING = {
    checkProductCode: function() {
        const productCode = $('#product_code').val();

        if (COMMON.isEmpty(productCode)) {
            alert('상품코드를 입력해 주세요.');
            $('#product_code').focus();
            return false;
        }

        $.ajax({
            url: '/api/stock/exist',
            data: {
                'productCode': productCode
            },
            type: 'POST',
            dataType: 'json',
            timeout: 5000,
            success: function(data) {
                if (data.status) {
                    if (data.isExist) {
                        $('#quantity_container').show();
                        $('#recommend_container').hide();
                        $('#quantity').val(0);
                        $('#quantity').focus();
                    } else {
                        alert('상품코드가 존재하지 않습니다.');
                        $('#quantity_container').hide();
                        $('#recommend_container').hide();
                        $('#product_code').focus();
                    }
                } else {
                    alert(data.msg);
                }
            },
            error: function(err) {
                alert(err);
            }
        });
    },
    receiveProduct: function() {
        const productCode = $('#product_code').val();
        const quantity = $('#quantity').val();

        if (COMMON.isEmpty(productCode)) {
            alert('상품코드를 입력해 주세요.');
            $('#product_code').focus();
            return false;
        }

        if (COMMON.isEmpty(quantity)) {
            alert('입고 수량을 입력해 주세요.');
            $('#quantity').focus();
            return false;
        }

        $.ajax({
            url: '/api/stock/receiving',
            data: {
                'productCode': productCode,
                'quantity': quantity
            },
            type: 'POST',
            dataType: 'json',
            timeout: 5000,
            success: function(data) {
                if (data.status) {
                    if (data.isReceived) {
                        location.href = `/stock/received/${data.stockId}`;
                    } else {
                        let html = '';
                        let skuLimit = '';
                        for (let i = 0; i < data.locations.length; i++) {
                            skuLimit = data.locations[i].sku_limit == 0 ? '제한없음' : data.locations[i].sku_limit;
                            html += '<tr>' +
                                '<td>' + (i + 1) + '</td>' +
                                '<td>' + data.locations[i].location_name + '</td>' +
                                '<td>' + data.locations[i].sku_cnt + '개 / ' + skuLimit + '개</td>' +
                                '<td><button type="button" onclick="PRODUCT_RECEIVING.confirmLocation(' + data.locations[i].location_id + ')">선택</button></td>' +
                                '</tr>';
                        }
                        $('#recommend_table > tbody').html(html);
                        $('#recommend_container').show();
                    }
                } else {
                    alert(data.msg);
                }
            },
            error: function(err) {
                alert(err);
            }
        });
    },
    confirmLocation: function(locationId) {
        const productCode = $('#product_code').val();
        const quantity = $('#quantity').val();

        if (COMMON.isEmpty(productCode)) {
            alert('상품코드를 입력해 주세요.');
            $('#product_code').focus();
            return false;
        }

        if (COMMON.isEmpty(quantity)) {
            alert('입고 수량을 입력해 주세요.');
            $('#quantity').focus();
            return false;
        }

        if (COMMON.isEmpty(locationId)) {
            alert('로케이션을 선택해 주세요.');
            return false;
        }

        $.ajax({
            url: '/api/stock/location',
            data: {
                'productCode': productCode,
                'quantity': quantity,
                'locationId': locationId
            },
            type: 'POST',
            dataType: 'json',
            timeout: 5000,
            success: function(data) {
                if (data.status) {
                    if (data.isReceived) {
                        location.href = `/stock/received/${data.stockId}`;
                    } else {
                        alert('현재 입고가 불가능한 로케이션입니다.\n다시 로케이션을 선택해 주세요.');
                        $('#recommend_table > tbody').html('');
                        $('#recommend_container').hide();
                        PRODUCT_RECEIVING.receiveProduct();
                    }
                } else {
                    alert(data.msg);
                }
            },
            error: function(err) {
                alert(err);
            }
        });
    }
};



