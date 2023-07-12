<div>
    <label for="product_code">상품코드</label>
    <input type="text" id="product_code" name="product_code" maxlength="20" value="" />
    <button type="button" onclick="PRODUCT_RECEIVING.checkProductCode();">확인</button>
</div>

<div class="receiving-container" id="quantity_container">
    <label for="quantity">입고 수량</label>
    <input type="number" id="quantity" name="quantity" min="0" value="" />
    <button type="button" onclick="PRODUCT_RECEIVING.receiveProduct()">확인</button>
</div>

<div class="receiving-container" id="recommend_container">
    <label for="recommend_table">추천 로케이션</label>
    <table id="recommend_table">
        <colgroup>
            <col style="width: 15%;"/>
            <col style="width: 40%;"/>
            <col style="width: 30%;"/>
            <col style="width: 15%;"/>
        </colgroup>
        <thead>
            <tr>
                <th>번호</th>
                <th>로케이션 명</th>
                <th>입고중인 SKU / SKU 제한</th>
                <th>입고</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>


