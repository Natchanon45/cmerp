<div class="table-responsive">
    <form id="summarize-data-form" name="summarize-data-form">
        <table id="summarize-data-table" class="display" cellspacing="0" width="100%">
            
            <!-- Header -->
                <thead>
                    <tr>
                        <th class="text-center w5p"><i class="fa fa-check-square-o" id="summarize-toggle-check" value="0"></th>
                        <th class="text-center w5p"><?php echo lang('id'); ?></th>
                        <th><?php echo lang('stock_material_production_name'); ?></th>
                        <th class="text-right w10p"><?php echo lang('quantity_of_shortage'); ?></th>
                        <th class="w5p"><?php echo lang('stock_material_unit'); ?></th>
                        <th class="text-center w5p"><i class="fa fa-bars"></i></th>
                    </tr>
                </thead>

            <!-- Detail -->
                <?php if ($summarize_data_list): ?>
                <tbody>
                    <?php foreach ($summarize_data_list as $data): ?>
                        <tr class="line-item-summary">
                            <td class="text-center w5p">
                                <input type="checkbox" id="material-id" name="material-id" value="<?php echo $data['id']; ?>">
                            </td>
                            <td class="text-center w5p"><?php echo $data['id']; ?></td>
                            <td><?php echo $data['material']; ?></td>
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
        setTimeout(() => {
            $('#summarize-data-table').DataTable({
                "bPaginate": true,
                "bLengthChange": true,
                "bFilter": true,
                "bSort": false,
                "bInfo": true,
                "bAutoWidth": false
            });

            $('#summarize-data-table_wrapper').prepend(submitButton);
            $('#summarize-data-table_length')[0].firstElementChild.lastChild.remove();
            $('#summarize-data-table_length')[0].firstElementChild.firstChild.remove();
            $('#summarize-data-table_length')[0].firstElementChild.firstChild.setAttribute('class', 'summarize-page-len');
            $('#summarize-data-table_info').css('margin', '.6rem 0 .6rem .6rem');
            $('#summarize-data-table_paginate').css('margin', '.6rem .6rem .7rem 0');
            $('.summarize-page-len').select2();

            let searchInput = $('#summarize-data-table_filter label input')[0];
            $('#summarize-data-table_filter label')[0].textContent = '<?php echo ucwords(lang('search')) . ':'; ?>';
            $('#summarize-data-table_filter label')[0].appendChild(searchInput);

            let tableInfo = $('#summarize-data-table_info')[0];
            tableInfo.textContent = tableInfo.textContent.replace('Showing', '<?php echo ucwords(lang('showing')); ?>');
            tableInfo.textContent = tableInfo.textContent.replace('to', '<?php echo strtolower(lang('to')); ?>');
            tableInfo.textContent = tableInfo.textContent.replace('of', '<?php echo strtolower(lang('of')); ?>');
            tableInfo.textContent = tableInfo.textContent.replace('entries', '<?php echo strtolower(lang('entries')); ?>');

            <?php if (!sizeof($summarize_data_list)): ?>
                $('.dataTables_empty')[0].textContent = '<?php echo lang('no_data_available'); ?>';
            <?php endif; ?>

            let leftAngle = document.createElement('i');
            leftAngle.classList = 'fa fa-angle-double-left fa-lg';
            leftAngle.setAttribute('aria-hidden', 'true');

            let rightAngle = document.createElement('i');
            rightAngle.classList = 'fa fa-angle-double-right fa-lg pointer';
            rightAngle.setAttribute('aria-hidden', 'true');

            let btnPrevious = $('#summarize-data-table_previous')[0];
            btnPrevious.textContent = btnPrevious.textContent.replace('Previous', '');
            btnPrevious.appendChild(leftAngle);

            let btnNext = $('#summarize-data-table_next')[0];
            btnNext.textContent = btnNext.textContent.replace('Next', '');
            btnNext.appendChild(rightAngle);

            setTimeout(() => {
                $('.panel').removeClass('hide');
            }, 25);
        }, 25);
    });

    // swal("Hello world!");

    function createSubmitButton() {
        let buttonSubmit = document.createElement('button');
        buttonSubmit.setAttribute('type', 'button');
        buttonSubmit.setAttribute('class', 'btn btn-danger');
        buttonSubmit.setAttribute('id', 'btn-submit');
        buttonSubmit.textContent = '<?php echo lang('to_issue_pr'); ?>';

        let divBtnSubmit = document.createElement('div');
        divBtnSubmit.setAttribute('class', 'dataTables_filter');
        divBtnSubmit.appendChild(buttonSubmit);

        return divBtnSubmit;
    };

    // btn-submit-click
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
                '<?php echo_uri('purchaserequests/pr_summarize'); ?>',
                { data: listChecked.data },
                'POST',
                '_self'
            );
        }
    });
    
    // line-item-click
    const lineItem = document.querySelectorAll('.line-item-summary');
    lineItem.forEach((element) => {
        element.setAttribute('style', 'cursor: pointer;');
        element.addEventListener('click', (event) => {
            event.preventDefault();

            let checkbox = element.firstElementChild.firstElementChild;
            checkbox.checked = !checkbox.checked;
        });
    });

    // btn-delete-click
    const btnDelete = document.querySelectorAll('.delete');
    btnDelete.forEach((element) => {
        element.addEventListener('click', (event) => {
            event.preventDefault();

            let lineId = element.firstElementChild.textContent;
            let line = element.parentElement.parentElement;
            let url = '<?php echo echo_uri('purchaserequests/dev2_deleteShortageByMaterialId'); ?>';

            if (confirm('Do you want to delete this item?')) {
                ApiFetchPostAsync(url, { id: lineId }).then((result) => {
                    // console.log(result);

                    if (result.success) {
                        line.remove();
                        appAlert.warning(
                            result.message,
                            { duration: 2500 }
                        );
                    } else {
                        appAlert.error(
                            result.message,
                            { duration: 2500 }
                        );
                    }
                });
            }
        });
    });

    // btn-toggle-click
    const btnToggleChecked = document.querySelector('#summarize-toggle-check');
    btnToggleChecked.addEventListener('click', (event) => {
        event.preventDefault();

        let current = parseInt(event.target.getAttribute('value'));
        if (current === 0) {
            event.target.setAttribute('value', '1');
            lineItem.forEach((element) => {
                element.firstElementChild.firstElementChild.checked = true;
            });
        } else {
            event.target.setAttribute('value', '0');
            lineItem.forEach((element) => {
                element.firstElementChild.firstElementChild.checked = false;
            });
        }
    });

    // api-fetch-post-async
    const ApiFetchPostAsync = async (url = "", data = {}) => {
        const response = await fetch(url, {
            method: "POST",
            mode: "cors",
            cache: "no-cache",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json"
            },
            redirect: "follow",
            referrerPolicy: "no-referrer",
            body: JSON.stringify(data)
        });
        return response.json();
    };
</script>

<style type="text/css">
    #summarize-data-table {
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

    .summarize-page-len {
        border-radius: 2px;
        padding: 7px 10px;
        outline: none;
        width: 80px;
        margin: .6rem 0rem 0rem .6rem;
        padding: 0;
    }

    #summarize-toggle-check {
        cursor: pointer;
    }
</style>