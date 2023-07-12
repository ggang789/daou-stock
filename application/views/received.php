<table>
    <colgroup>
        <col style="width: 30%" />
        <col style="width: 70%" />
    </colgroup>
    <tbody>
        <tr>
            <th>입고 상품명</th>
            <td><?=$received_info['product_name']?></td>
        </tr>
        <tr>
            <th>입고 수량</th>
            <td><?=number_format($received_info['quantity'])?>개</td>
        </tr>
        <tr>
            <th>입고 로케이션명</th>
            <td><?=$received_info['location_name']?></td>
        </tr>
    </tbody>
</table>

<button type="button" id="go-to-first-page-btn" onclick="location.href='/';">첫 페이지로 돌아가기</button>