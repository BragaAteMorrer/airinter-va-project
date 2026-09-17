<div class="rule-builder" data-rule-builder>
    <label>Combinaison <select name="operator"><option value="and">Tous les critères (AND)</option><option value="or">Au moins un critère (OR)</option></select></label>
    <input type="hidden" name="rule_id" data-rule-id>
    <aside class="mini-bulletin" data-catalogue-info aria-live="polite"></aside>
    <div class="rule-rows" data-rule-rows></div>
    <template data-rule-template>
        <div class="rule-row" data-rule-row>
            <label>Critère <select name="criteria[__INDEX__][metric]" data-metric><option value="validated_flights">Vols validés</option><option value="flight_minutes">Minutes de vol</option><option value="total_distance">Distance totale (NM)</option><option value="visited_airports">Aéroports visités</option><option value="visited_countries">Pays visités</option><option value="seniority_days">Ancienneté (jours)</option><option value="required_badge">Badge déjà obtenu</option><option value="route">Ligne spécifique</option><option value="airline">Compagnie</option><option value="aircraft_icao">Type d’appareil</option><option value="event_completed">Événement complété</option><option value="night_flights">Vols de nuit</option></select></label>
            <label data-number>Seuil <input name="criteria[__INDEX__][value]" data-number-value type="number" min="0" value="10"></label>
            <label data-award hidden>Badge requis <select data-award-value><?php $__currentLoopData = $awards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $award): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($award->id); ?>"><?php echo e($award->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
            <label data-airline hidden>Compagnie <select data-airline-value><?php $__currentLoopData = $airlines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $airline): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($airline->id); ?>"><?php echo e($airline->icao); ?> · <?php echo e($airline->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
            <label data-aircraft hidden>Appareil <select data-aircraft-value><?php $__currentLoopData = $aircraftTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($type); ?>"><?php echo e($type); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
            <label data-event hidden>Événement <select data-event-value><?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($event->id); ?>"><?php echo e($event->title); ?> · <?php echo e($event->starts_at); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
            <label data-text hidden>Valeur <input name="criteria[__INDEX__][text]" data-text-value maxlength="30" placeholder="Code ligne ou ICAO"></label>
            <button type="button" class="button outline" data-remove-criterion>Retirer</button>
        </div>
    </template>
    <button type="button" class="button outline" data-add-criterion>+ Ajouter un critère</button>
</div>
<?php if (! $__env->hasRenderedOnce('e9e48f4f-a4fa-4d72-83c2-17196bc3cdc1')): $__env->markAsRenderedOnce('e9e48f4f-a4fa-4d72-83c2-17196bc3cdc1'); ?>
<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('[data-rule-builder]').forEach((builder) => {
  const rows = builder.querySelector('[data-rule-rows]'), template = builder.querySelector('[data-rule-template]').innerHTML;
  let index = 0;
  const syncRow = (row) => {
    const metric = row.querySelector('[data-metric]').value, value = row.querySelector('[data-number-value]'), text = row.querySelector('[data-text-value]');
    row.querySelector('[data-number]').hidden = ['required_badge','route','airline','aircraft_icao','event_completed'].includes(metric);
    row.querySelector('[data-award]').hidden = metric !== 'required_badge'; row.querySelector('[data-airline]').hidden = metric !== 'airline'; row.querySelector('[data-aircraft]').hidden = metric !== 'aircraft_icao'; row.querySelector('[data-event]').hidden = metric !== 'event_completed'; row.querySelector('[data-text]').hidden = !['route','aircraft_icao'].includes(metric);
    if (metric === 'required_badge') value.value = row.querySelector('[data-award-value]').value;
    if (metric === 'airline') value.value = row.querySelector('[data-airline-value]').value;
    if (metric === 'event_completed') value.value = row.querySelector('[data-event-value]').value;
  };
  const add = (criterion = null) => { rows.insertAdjacentHTML('beforeend', template.replaceAll('__INDEX__', index++)); const row = rows.lastElementChild; if (criterion) { row.querySelector('[data-metric]').value = criterion.metric; row.querySelector('[data-number-value]').value = criterion.value ?? 0; row.querySelector('[data-text-value]').value = criterion.text ?? criterion.route ?? ''; if (criterion.metric === 'required_badge') row.querySelector('[data-award-value]').value = criterion.value; if (criterion.metric === 'airline') row.querySelector('[data-airline-value]').value = criterion.value; if (criterion.metric === 'event_completed') row.querySelector('[data-event-value]').value = criterion.value; if (criterion.metric === 'aircraft_icao') row.querySelector('[data-aircraft-value]').value = criterion.text ?? ''; } syncRow(row); };
  builder.querySelector('[data-add-criterion]').addEventListener('click', add);
  builder.addEventListener('change', (event) => { const row = event.target.closest('[data-rule-row]'); if (row) syncRow(row); });
  rows.addEventListener('click', (event) => { if (event.target.matches('[data-remove-criterion]') && rows.children.length > 1) event.target.closest('[data-rule-row]').remove(); });
  const form = builder.closest('form'), kind = form.dataset.ruleKind, target = form.querySelector(kind === 'badge' ? '[name="award_id"]' : '[name="rank_id"]');
  const updateInfo = () => { const item = window.prometheeAutomationCatalogue?.[kind]?.[target.value], info = builder.querySelector('[data-catalogue-info]'); info.replaceChildren(); if (!item) return; if (item.image_url) { const image=document.createElement('img'); image.src=item.image_url; image.alt=''; image.style.maxHeight='56px'; image.style.maxWidth='96px'; info.append(image); } const title=document.createElement('strong'); title.textContent=item.name; info.append(title); const detail=document.createElement('span'); detail.textContent=kind === 'rank' ? `Seuil de référence : ${item.hours} h` : (item.description || 'Aucune description.'); info.append(detail); };
  const renderRule = (rule) => { rows.innerHTML = ''; index = 0; form.querySelector('[data-rule-id]').value = rule?.id ?? ''; form.querySelector('[name="operator"]').value = rule?.operator ?? 'and'; form.querySelector('[name="active"]').checked = rule ? !!rule.active : true; const demotion = form.querySelector('[name="allow_demotion"]'); if (demotion) demotion.checked = !!rule?.allow_demotion; (rule?.criteria?.length ? rule.criteria : [{metric:'validated_flights',value:10}]).forEach(add); updateInfo(); };
  const loadRule = async () => { const selected = target.value; renderRule(window.prometheeAutomationRules?.[kind]?.[selected]); try { const response = await fetch(`/admin/promethee/automation/rules/${kind}/${selected}`, {headers:{Accept:'application/json'}}); if (response.ok && target.value === selected) { renderRule(await response.json()); } } catch (_) { /* The embedded data remains a safe offline fallback. */ } };
  target.addEventListener('change', loadRule); loadRule();
}));
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH /home/jewe0363/promethee/modules/Promethee/Providers/../Resources/views/admin/rule-fields.blade.php ENDPATH**/ ?>