<fieldset><legend>Traçabilité</legend>
<label>Statut <select name="historical_confidence">@foreach($confidenceOptions as $confidence)<option value="{{ $confidence }}" @selected($confidence==='VA_configuration')>{{ $confidence }}</option>@endforeach</select></label>
<label>Source <input name="source" placeholder="Archive, ouvrage, fiche constructeur…"></label>
<label>URL source <input type="url" name="source_url"></label>
<label>Notes <textarea name="notes" rows="2"></textarea></label>
</fieldset>
