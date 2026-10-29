namespace Promethee;

/// <summary>
/// Determines whether the aircraft is secured at the stand without pretending
/// that wheel chocks engage the actual parking brake. Unknown is never safe.
/// Only the Fenix A319/A320/A321 family may use its vendor-specific variables.
/// </summary>
public static class AircraftGroundSafety
{
    public static bool IsFenixIdentity(string? title, string? icao = null, string? model = null)
    {
        var identity = string.Join(" ", title, icao, model);
        return identity.Contains("Fenix", StringComparison.OrdinalIgnoreCase)
            && (identity.Contains("A319", StringComparison.OrdinalIgnoreCase)
                || identity.Contains("A320", StringComparison.OrdinalIgnoreCase)
                || identity.Contains("A321", StringComparison.OrdinalIgnoreCase));
    }

    public static bool IsFenix(AircraftSnapshot snapshot) =>
        IsFenixIdentity(snapshot.AircraftTitle, snapshot.AircraftIcao, snapshot.AircraftModel);

    public static bool? IsSecured(AircraftSnapshot snapshot)
    {
        if (snapshot.ParkingBrake == true) return true;

        if (IsFenix(snapshot))
        {
            if (snapshot.FenixParkingBrake == true || snapshot.WheelChocks == true)
                return true;

            if (snapshot.ParkingBrake == false || snapshot.FenixParkingBrake == false || snapshot.WheelChocks == false)
                return false;
        }

        return snapshot.ParkingBrake;
    }
}
