{if !empty({$error})} <div class="alert alert-{if $status == 1}success{else}danger{/if}">{$error}</div> {/if}
<div class="col-md-6">
<div class="panel">
    <div class="panel-heading">
        <i class="icon-envelope"></i>
        <label>{l s="Send SMS to Customer" mod='bulksmsforall'}</label>
    </div>
<form action="index.php?controller=AdminOrders&id_order={$smarty.get.id_order}&vieworder&token={$smarty.get.token}&sendsms=1" method="post">
    {l s="Phone Number" mod='bulksmsforall'}: {$delivery_phone}<br>
<textarea placeholder="{l s="Message" mod='bulksmsforall'}" name="smsmessage">{$message}</textarea><br>

    <div class="text-right">
        <a href="https://wa.me/{$waphone}" target="new" class="btn btn-success pull-left">{l s="Click to Chat Whatsapp" mod='bulksmsforall'}</a>
    <button type="submit" class="btn btn-primary">{l s="Send SMS" mod='bulksmsforall'}</button>
    </div>

</form>

</div>
</div>
<div class="col-md-6">
    <div class="panel">
        <div class="panel-heading">

            <label>{l s="Delivery Informations for Shipping (bulksmsforall extra feature)" mod='bulksmsforall'}</label>
        </div>
        <form>
            <label>{l s="Delivery Name" mod='bulksmsforall'} <a class="btn btn-default" onclick="copyit('namex');" >{l s="Copy" mod='bulksmsforall'}</a></label>
    <input name="namex" id="namex" placeholder=" " class="form-control"  value="{$delivery_firstname} {$delivery_lastname}" readonly>
            <label>{l s="Delivery Address" mod='bulksmsforall'} <a class="btn btn-default"  onclick="copyit('addressx');" >{l s="Copy" mod='bulksmsforall'}</a></label>
        <textarea name="addressx" placeholder=" "  class="form-control" id="addressx" readonly>{$delivery_company}
{$delivery_fulladres}</textarea>
            <label>{l s="Delivery Phone" mod='bulksmsforall'} <a class="btn btn-default"  onclick="copyit('phonex');" >{l s="Copy" mod='bulksmsforall'}</a></label>
        <input name="phonex"  class="form-control" id="phonex" placeholder=" " value="{$delivery_phone}" readonly>
        </form>
        <code id="infox"></code>
    </div>
</div>
<script>
    function copyit(id) {
        var copyText = document.getElementById(id);

        /* Select the text field */
        copyText.select();

        /* Copy the text inside the text field */
        document.execCommand("copy");

        /* Alert the copied text */
        $('#infox').html('{l s="Copied the text: " mod="bulksmsforall"}' + copyText.value);
        //alert("Copied the text: " + copyText.value );
    }
</script>