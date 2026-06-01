<div class="modal fade" id="addRSModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rs-box">

            <!-- HEADER -->
            <div class="rs-head">
                <h5>
                    <i class="feather-file-text me-2 text-primary"></i>
                    Request Slip
                    <span style="font-size:11px;">
                        : {{ old('requisition_slip_no', $nextSlipNo ?? '') }}
                    </span>
                </h5>

                <button type="button" class="rs-close" data-bs-dismiss="modal">&times;</button>
            </div>

            <!-- BODY -->
            <div class="rs-body">

                <form id="rsForm" method="POST" action="{{ route('request-slip.store') }}">
                    @csrf

                    <input type="hidden" name="employee_id" value="{{ Auth::id() }}">
                    <input type="hidden" name="department_id" value="{{ Auth::user()->department_id }}">

                    <div class="rs-grid">

                        <div class="rs-field d-none">
                            <input type="text" name="requisition_slip_no"
                                   value="{{ old('requisition_slip_no', $nextSlipNo ?? '') }}"
                                   class="rs-control" readonly>
                        </div>

                        <div class="rs-field">
                            <label>Date *</label>
                            <input type="date" name="transaction_date" class="rs-control"
                                   value="{{ old('transaction_date', date('Y-m-d')) }}">
                            <small class="error-msg"></small>
                        </div>

                        <div class="rs-field">
                            <label>Project *</label>
                            <select id="project_id" name="project_id" class="rs-control">
                                <option value="">Select Project</option>
                                @foreach($modelprojects as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                            <small class="error-msg"></small>
                        </div>

                        <div class="rs-field">
                            <label>Comment</label>
                            <input type="text" name="comment" class="rs-control">
                        </div>

                    </div>

                    <!-- ITEMS -->
                    <div id="rs_items"></div>

                    <button type="button" id="addRowBtn" class="rs-add-btn">
                        + Add Item
                    </button>

                    <div class="rs-foot">
                        <button type="button" class="rs-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="rs-save">Save</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

{{-- ================= CSS ================= --}}
<style>
.rs-head{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:14px;
    background:#f8fafc;
    border-bottom:1px solid #e2e8f0;
}

.rs-head h5{
    font-size:15px;
    font-weight:700;
    margin:0;
}

.rs-close{
    width:28px;
    height:28px;
    background:#fee2e2;
    border:none;
    border-radius:6px;
}

.rs-body{padding:16px;}

.rs-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:12px;
}

.rs-control{
    width:100%;
    border:1px solid #e2e8f0;
    padding:6px 10px;
    border-radius:6px;
    font-size:13px;
}

.rs-field label{
    font-size:11px;
    font-weight:700;
    color:#64748b;
}

.rs-item{
    border:1px solid #e2e8f0;
    padding:10px;
    margin-top:10px;
    border-radius:8px;
}

.rs-add-btn{
    width:100%;
    border:1px dashed #2563eb;
    background:#f1f5ff;
    padding:7px;
    border-radius:6px;
    margin-top:10px;
    color:#2563eb;
}

.rs-save{
    background:#16a34a;
    color:#fff;
    padding:8px 16px;
    border:none;
    border-radius:6px;
}

.rs-cancel{
    background:#f1f5f9;
    padding:8px 16px;
    border:1px solid #e2e8f0;
    border-radius:6px;
}

.rs-foot{
    display:flex;
    justify-content:flex-end;
    gap:10px;
    padding-top:12px;
}

.error-msg{
    font-size:11px;
    color:red;
}

@media(max-width:600px){
    .rs-grid{grid-template-columns:1fr;}
}
</style>

{{-- ================= JS ================= --}}
<script>
let machinesOptionsHtml = `<option value="">Select Machine</option>`;

/* MOVE MODAL TO BODY */
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById('addRSModal');
    if (modal) document.body.appendChild(modal);
});

/* INIT SELECT2 */
function initSelect2($el){
    if($.fn.select2){
        if($el.hasClass("select2-hidden-accessible")){
            $el.select2('destroy');
        }

        $el.select2({
            width:'100%',
            dropdownParent: $('#addRSModal')
        });
    }
}

/* ERROR */
function showError(el,msg){
    $(el).closest('.rs-field').find('.error-msg').text(msg);
}

function clearErrors(){
    $('.error-msg').text('');
}

function isEmpty(v){
    return !v || v.toString().trim()==='';
}

/* BUILD MACHINES */
function buildMachinesOptions(machines){
    machinesOptionsHtml = `<option value="">Select Machine</option>`;
    machines.forEach(m=>{
        machinesOptionsHtml += `<option value="${m.id}">${m.name}</option>`;
    });
}

/* PROJECT CHANGE */
$(document).on('change','#project_id',function(){

    let projectId = $(this).val();
    if(!projectId) return;

    $.get("/inventory/request-slip/products/"+projectId,function(res){

        buildMachinesOptions(res);

        $('.machine-select').each(function(){
            $(this).html(machinesOptionsHtml);
            initSelect2($(this));
        });

        if($('#rs_items .rs-item').length===0){
            addRow();
        }
    });
});

/* ADD ROW */
function addRow(){

    let html = `
    <div class="rs-item">
        <div class="rs-grid">

            <div class="rs-field">
                <label>Machine</label>
                <select class="rs-control machine-select">
                    ${machinesOptionsHtml}
                </select>
                <small class="error-msg"></small>
            </div>

            <div class="rs-field">
                <label>Inventory</label>
                <select class="rs-control item-select">
                    <option value="">Select</option>
                </select>
                <small class="error-msg"></small>
            </div>

            <div class="rs-field">
                <label>Qty</label>
                <input type="number" class="rs-control qty-input">
                <small class="error-msg"></small>
            </div>

        </div>
    </div>`;

    let $row = $(html);
    $('#rs_items').append($row);

    initSelect2($row.find('.machine-select'));
    initSelect2($row.find('.item-select'));
}

/* ADD BUTTON */
$(document).on('click','#addRowBtn',function(){
    addRow();
});

/* MACHINE → INVENTORY */
$(document).on('change','.machine-select',function(){

    let machineId = $(this).val();
    let row = $(this).closest('.rs-item');
    let inv = row.find('.item-select');

    inv.html('<option>Loading...</option>');

    if(!machineId){
        inv.html('<option value="">Select</option>');
        initSelect2(inv);
        return;
    }

    $.get(`/inventory/request-slip/product-items/${machineId}`,function(res){

        let options = `<option value="">Select</option>`;

        res.forEach(r=>{
            if(!r.inventory) return;

            options += `<option value="${r.inventory.id}" data-need="${r.need_qty}">
                ${r.inventory.name}
            </option>`;
        });

        inv.html(options);
        initSelect2(inv);
    });
});

/* VALIDATION */
$('#rsForm').on('submit',function(e){
    e.preventDefault();
    clearErrors();

    let ok=true;

    if(isEmpty($('#project_id').val())){
        showError('#project_id','Project required');
        ok=false;
    }

    $('.rs-item').each(function(){

        let m=$(this).find('.machine-select').val();
        let i=$(this).find('.item-select').val();
        let q=$(this).find('.qty-input').val();

        if(isEmpty(m)){showError($(this).find('.machine-select'),'Required');ok=false;}
        if(isEmpty(i)){showError($(this).find('.item-select'),'Required');ok=false;}
        if(isEmpty(q)||q<=0){showError($(this).find('.qty-input'),'Invalid');ok=false;}
    });

    if(ok)this.submit();
});
</script>