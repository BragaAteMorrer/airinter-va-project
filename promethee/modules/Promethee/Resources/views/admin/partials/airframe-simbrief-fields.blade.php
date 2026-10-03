<fieldset><legend>SimBrief</legend>
<label>Stratégie <select name="simbrief_strategy"><option value="">Hériter</option>@foreach($strategyOptions as $strategy)<option value="{{ $strategy }}">{{ $strategy }}</option>@endforeach</select></label>
<label>Type <input name="simbrief_type" placeholder="N262"></label>
<label>Internal ID <input name="simbrief_internal_id"></label>
<label>Proxy <input name="simbrief_proxy_type" placeholder="SH33"></label>
</fieldset>
