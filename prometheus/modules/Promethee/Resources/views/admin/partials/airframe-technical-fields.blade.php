<fieldset><legend>Données techniques (laisser vide = hériter)</legend>
<label>PAX max <input type="number" min="0" name="max_pax"></label><label>Cabine <input name="seat_configuration"></label>
<label>Unité des masses <select name="weight_unit"><option value="">Hériter / non précisée</option><option value="kg">kg</option><option value="lb">lb</option><option value="klb">milliers de lb</option></select><small>Obligatoire pour envoyer les masses via SimBrief acdata.</small></label>
<label>Catégorie de turbulence <select name="weight_category"><option value="">Hériter</option><option>L</option><option>M</option><option>H</option><option>J</option></select></label>
<label>OEW <input type="number" step="0.01" min="0" name="oew"></label><label>MZFW <input type="number" step="0.01" min="0" name="mzfw"></label>
<label>MTOW <input type="number" step="0.01" min="0" name="mtow"></label><label>MLW <input type="number" step="0.01" min="0" name="mlw"></label>
<label>Carburant max <input type="number" step="0.01" min="0" name="max_fuel"></label><label>Cargo max <input type="number" step="0.01" min="0" name="max_cargo"></label>
<label>Motoriste <input name="engine_manufacturer"></label><label>Moteur <input name="engine_model"></label>
<label>Variante moteur <input name="engine_variant"></label><label>Nombre <input type="number" min="0" max="16" name="engine_count"></label>
<label>Libellé moteur SimBrief <input name="engine_simbrief_label"></label>
<label>Vitesse croisière <input type="number" step="0.01" min="0" name="cruise_speed"></label><label>Mach <input type="number" step="0.001" min="0" max="2" name="cruise_mach"></label>
<label>Plafond <input type="number" step="1" min="0" name="ceiling"></label><label>Autonomie <input type="number" step="1" min="0" name="range"></label>
<label>Équipement <input name="equipment"></label><label>Transpondeur <input name="transponder"></label><label>PBN <input name="pbn"></label>
<label>Fuel factor (%) <input type="number" step="0.1" min="-50" max="100" name="fuel_factor"></label>
<label>Profil montée <input name="climb_profile"></label><label>Profil croisière <input name="cruise_profile"></label><label>Profil descente <input name="descent_profile"></label>
</fieldset>
