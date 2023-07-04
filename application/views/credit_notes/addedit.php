<style type="text/css">


    
.popup .invoice {
    height: 280px;
    border: 1px solid #ccc;
    overflow: auto;
}

.popup .invoice table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}


.popup .invoice thead tr th {
    position: sticky;
    top: 0;
    font-weight: 500;
}

.popup .invoice tbody tr {
    border-bottom: 1px solid #e8e8e8;
}

.popup .invoice tbody tr:hover {
    background: #e6f7ff;
}

.popup .invoice th{
    border-bottom: 1px solid #e8e8e8;
    text-align: left;
    color: #fff;
    padding: 6px 3px;
}

.popup .invoice td{
    vertical-align: top;
    padding: 6px 3px;
    text-align: left;
}

.popup .invoice tbody tr:last-child{
    border-bottom: 0;
}

.popup .invoice th:nth-child(1),
.popup .invoice td:nth-child(1){
    padding-left: 8px;
}
</style>
<div class="popup">
    <div class="container">
        <div class="customer">
            <label>ค้นหาชื่อลูกค้า</label>
            <select>
                <option>TEST</option>
            </select>
        </div>
        <div class="invoice">
            <table>
                <thead>
                    <tr>
                        <th class="custom-bg">วันที่</th>
                        <th class="custom-bg">เลขที่เอกสาร</th>
                        <th class="custom-bg">ชื่อลูกค้า</th>
                        <th class="custom-bg">จำนวนเงิน</th>
                        <th class="custom-bg">สถานะ</th>
                        <th class="custom-bg"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>xx</td>
                        <td>yy</td>
                        <td>zz</td>
                        <td>aa</td>
                        <td>bb</td>
                        <td>cc</td>
                    </tr>
                    <tr>
                        <td>xx</td>
                        <td>yy</td>
                        <td>zz</td>
                        <td>aa</td>
                        <td>bb</td>
                        <td>cc</td>
                    </tr>
                    <tr>
                        <td>xx</td>
                        <td>yy</td>
                        <td>zz</td>
                        <td>aa</td>
                        <td>bb</td>
                        <td>cc</td>
                    </tr>
                    <tr>
                        <td>xx</td>
                        <td>yy</td>
                        <td>zz</td>
                        <td>aa</td>
                        <td>bb</td>
                        <td>cc</td>
                    </tr>
                    <tr>
                        <td>xx</td>
                        <td>yy</td>
                        <td>zz</td>
                        <td>aa</td>
                        <td>bb</td>
                        <td>cc</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-close"></span>ปิดหน้าต่าง</button>
    </div>
</div>