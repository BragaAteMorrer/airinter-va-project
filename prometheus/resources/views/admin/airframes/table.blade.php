<div id="airframes_table_wrapper">
  <table class="table table-hover table-responsive">
    <thead>
      <th>ICAO</th>
      <th>Name</th>
      <th>Mode SimBrief</th>
      <th>Profil calcul</th>
      <th>Created At</th>
      <th>Updated At</th>
      <th></th>
    </thead>
    <tbody>
      @foreach($airframes as $af)
        <tr>
          <td>{{ $af->icao }}</a></td>
          <td>{{ $af->name }}</td>
          @php($sb = $af->simbriefProfile())
          <td>{{ $sb['strategy'] }}</td>
          <td>
            @if($sb['strategy'] === 'proxy')
              {{ $sb['proxy_type'] }} <small class="text-muted">(proxy)</small>
            @elseif($sb['strategy'] === 'custom_airframe')
              {{ $sb['internal_id'] ?: 'Internal ID manquant' }}
            @else
              {{ $af->icao }}
            @endif
          </td>
          <td>{{ $af->created_at->format('d.M.y H:i') }}</td>
          <td>{{ $af->updated_at->format('d.M.y H:i') }}</td>
          <td class="text-right">
            {{ Form::open(['route' => ['admin.airframes.destroy', $af->id], 'method' => 'delete']) }}
            <a href="{{ route('admin.airframes.edit', [$af->id]) }}" class='btn btn-sm btn-success btn-icon'>
              <i class="fas fa-pencil-alt"></i></a>
            {{ Form::button('<i class="fa fa-times"></i>', ['type' => 'submit', 'class' => 'btn btn-sm btn-danger btn-icon', 'onclick' => "return confirm('Are you sure?')"]) }}
            {{ Form::close() }}
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
