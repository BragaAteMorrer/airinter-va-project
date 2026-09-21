@if(filled($deleted_airports))
  <div style="margin-bottom: 5px;">
    <table class="table table-borderless table-hover small text-left" style="margin-bottom: 1px;">
      <tr class="bg-warning">
        <th class="text-center" colspan="7"><b>Soft Deleted Airports</b></th>
      </tr>
      <tr>
        <th>ID</th>
        <th>ICAO</th>
        <th>IATA</th>
        <th>Name</th>
        <th>Country</th>
        <th>Deleted At</th>
        <th class="text-right">Action</th>
      </tr>
      @foreach($deleted_airports as $airport)
        <tr>
          <td>{{ $airport->id }}</td>
          <td>{{ $airport->icao }}</td>
          <td>{{ $airport->iata }}</td>
          <td>{{ $airport->name }}</td>
          <td>{{ $airport->country }}</td>
          <td>{{ $airport->deleted_at->format('d.m.Y H:i') }}</td>
          <td class="text-right">
            <a href="{{ route('DAirports.restore_airport', ['id' => $airport->id]) }}" class="btn btn-success btn-sm mx-1">Restore</a>
            <a href="{{ route('DAirports.destroy_airport', ['id' => $airport->id]) }}" class="btn btn-danger btn-sm mx-1" onclick="return confirm('This will delete the airport record !!!\n\n Are you sure ?')">Delete</a>
          </td>
        </tr>
      @endforeach
    </table>
  </div>
@else
  <p>No soft deleted airports found.</p>
@endif