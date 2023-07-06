<div class="table-responsive">
    <form id="records-data-form" name="records-data-form">
        <table id="records-data-table" class="display" cellspacing="0" width="100%">
            <?php if (sizeof($records_data_list)): ?>
                <thead>
                    <tr>
                        <th class="text-center w5p"><i class="fa fa-check-square-o"></th>
                        <th class="text-center w5p"><?php echo lang('id'); ?></th>
                        <th><?php echo lang('project_name'); ?></th>
                        <th><?php echo lang('stock_material_production_name'); ?></th>
                        <th class="w10p"><?php echo lang('material_shortage_date'); ?></th>
                        <th class="text-right w10p"><?php echo lang('quantity_of_shortage'); ?></th>
                        <th class="w5p"><?php echo lang('stock_material_unit'); ?></th>
                        <th class="text-center w5p"><i class="fa fa-bars"></i></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($records_data_list as $data): ?>
                        <tr class="line-item">
                            <td class="text-center w5p">
                                <input type="checkbox" id="bpim-id" name="bpim-id" value="<?php echo $data['id']; ?>">
                            </td>
                            <td class="text-center w5p"><?php echo $data['id']; ?></td>
                            <td><?php echo $data['project']; ?></td>
                            <td><?php echo $data['material']; ?></td>
                            <td><?php echo $data['created']; ?></td>
                            <td class="text-right decimal-shortage"><?php echo $data['ratio']; ?></td>
                            <td><?php echo $data['unit']; ?></td>
                            <td class="option text-center">
                                <a class="delete" value="<?php echo $data['id']; ?>">
                                    <span style="display: none;"><?php echo $data['id']; ?></span>
                                    <i class='fa fa-times fa-fw'></i>
                                </a>
                            </td>
                        </tr>
                <?php endforeach; ?>
                </tbody>
            <?php endif; ?>
        </table>
    </form>
</div>

<script src="/assets/js/jquery.redirect.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.panel').addClass('hide');

        setTimeout(() => {
            $('#records-data-table').DataTable({
                "bPaginate": true,
                "bLengthChange": true,
                "bFilter": true,
                "bSort": false,
                "bInfo": true,
                "bAutoWidth": false
            });

            $('#records-data-table_wrapper').prepend(submitButton);
            $('#records-data-table_length')[0].firstElementChild.lastChild.remove();
            $('#records-data-table_length')[0].firstElementChild.firstChild.remove();
            $('#records-data-table_length')[0].firstElementChild.firstChild.setAttribute('class', 'page-len');
            $('#records-data-table_info').css('margin', '.6rem 0 .6rem .6rem');
            $('#records-data-table_paginate').css('margin', '.6rem .6rem .7rem 0');
            $('.page-len').select2();

            setTimeout(() => {
                $('.panel').removeClass('hide');
            }, 25);
        }, 25);
    });
    
    const submitButton = createSubmitButton();
    submitButton.addEventListener('click', (event) => {
        event.preventDefault();
        let listChecked = {
            data: []
        };

        lineItem.forEach((element) => {
            let checkbox = element.firstElementChild.firstElementChild;
            if (checkbox.checked) {
                listChecked.data.push(checkbox.value);
            }
        });

        if (listChecked.data.length) {
            $.redirect(
                '<?php echo_uri('purchaserequests/prcreatebyid'); ?>',
                { data: JSON.stringify(listChecked.data) },
                'POST',
                '_self'
            );
        }
    });

    const formData = document.querySelector('#records-data-form');
    const lineItem = document.querySelectorAll('.line-item');
    lineItem.forEach((element) => {
        element.setAttribute('style', 'cursor: pointer;');
        element.addEventListener('click', (event) => {
            event.preventDefault();

            let checkbox = element.firstElementChild.firstElementChild;
            checkbox.checked = !checkbox.checked;
        });
    });

    const btnDelete = document.querySelectorAll('.delete');
    btnDelete.forEach((element) => {
        element.addEventListener('click', (event) => {
            event.preventDefault();

            let lineId = element.firstElementChild.textContent;
            let line = element.parentElement.parentElement;

            if (confirm('Do you want to delete this item?')) {
                line.remove();
            }
        });
    });

    function createSubmitButton() {
        let divBtnSubmit = document.createElement('div');
        let buttonSubmit = document.createElement('button');

        buttonSubmit.setAttribute('type', 'button');
        buttonSubmit.setAttribute('class', 'btn btn-danger');
        buttonSubmit.setAttribute('id', 'btn-submit');
        buttonSubmit.textContent = '<?php echo lang('to_issue_pr'); ?>';

        divBtnSubmit.setAttribute('class', 'dataTables_filter');
        divBtnSubmit.appendChild(buttonSubmit);

        return divBtnSubmit;
    };
</script>

<style type="text/css">
    #records-data-table {
        font-size: small;
    }

    .decimal-shortage {
        color: #ff3131;
        font-weight: bold;
    }

    .dataTables_filter {
        margin: .6rem 0.6rem 0.2rem .5rem;
    }

    @keyframes fade-in {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .panel {
        animation-name: fade-in;
        animation-duration: 1.2s;
    }

    .page-len {
        border-radius: 2px;
        padding: 7px 10px;
        outline: none;
        width: 80px;
        margin: .6rem 0rem 0rem .6rem;
        padding: 0;
    }
</style>