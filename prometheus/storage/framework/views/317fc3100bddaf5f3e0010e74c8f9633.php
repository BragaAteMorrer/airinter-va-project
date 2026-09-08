<table class="table" style="border-style: hidden; margin-bottom: 0; padding:0;background-color:transparent;">
  <tr style="border-style: hidden;">
    <?php if($pirep->state === PirepState::PENDING || $pirep->state === PirepState::REJECTED): ?>
      <td>
        <?php echo e(Form::open(['url' => route('admin.pirep.status', [$pirep->id]),
                        'method' => 'post',
                        'name' => 'accept_'.$pirep->id,
                        'id' => $pirep->id.'_accept',
                        'pirep_id' => $pirep->id,
                        'new_status' => PirepState::ACCEPTED,
                        'class' => $on_edit_page ? 'pirep_change_status': 'pirep_submit_status'])); ?>

        <?php echo e(Form::button('Accept', ['type' => 'submit', 'class' => 'btn btn-success'])); ?>

        <?php echo e(Form::close()); ?>

      </td>
    <?php endif; ?>
    <?php if($pirep->state === PirepState::PENDING || $pirep->state === PirepState::ACCEPTED): ?>
      <td>
        <?php echo e(Form::open(['url' => route('admin.pirep.status', [$pirep->id]),
                        'method' => 'post',
                        'name' => 'reject_'.$pirep->id,
                        'id' => $pirep->id.'_reject',
                        'pirep_id' => $pirep->id,
                        'new_status' => PirepState::REJECTED,
                        'class' => $on_edit_page ? 'pirep_change_status': 'pirep_submit_status'])); ?>

        <?php echo e(Form::button('Reject', ['type' => 'submit', 'class' => 'btn btn-warning'])); ?>

        <?php echo e(Form::close()); ?>

      </td>
    <?php endif; ?>
    <?php if($on_edit_page === false): ?>
      <td>
        <form action="<?php echo e(route('admin.pireps.edit', [$pirep->id])); ?>">
          <button type="submit" class='btn btn-info'>Edit</button>
        </form>
      </td>
    <?php endif; ?>
    <td>
      <form action="<?php echo e(route('frontend.pireps.show', [$pirep->id])); ?>" target="_blank">
        <button type="submit" class='btn btn-success'>View</button>
      </form>
    </td>
    <td>
      <?php echo e(Form::open(['url' => route('admin.pireps.destroy', [$pirep->id]),
            'method' => 'delete',
            'name' => 'delete_'.$pirep->id,
            'id' => $pirep->id.'_delete',
            'onclick' => "return confirm('Are you sure?')"
            ])); ?>

        <?php echo e(Form::button('Delete', ['type' => 'submit', 'class' => 'btn btn-danger'])); ?>

        <?php echo e(Form::close()); ?>

    </td>
  </tr>
</table>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/pireps/actions.blade.php ENDPATH**/ ?>