using System.Text.Json;
using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

public sealed class AircraftCapabilitiesTests
{
    private static readonly SimulatorDescriptor XPlaneLike = new(
        SimulatorKind.XPlane,
        "X-Plane test",
        "xplane-test",
        SimulatorCapabilities.Position |
        SimulatorCapabilities.FlightDynamics |
        SimulatorCapabilities.Fuel |
        SimulatorCapabilities.AircraftSystems);

    [Fact]
    public void Report_distinguishes_supported_unknown_and_connector_unsupported()
    {
        var monitor = new AircraftCapabilityMonitor();
        var report = monitor.Observe(XPlaneLike, Snapshot(
            title: "Generic Test Aircraft",
            bank: null,
            fuel: 8200,
            gear: null));

        Assert.Equal(CapabilityAvailability.Supported, report.Get(AircraftDataCapability.Fuel));
        Assert.Equal(CapabilityAvailability.Unknown, report.Get(AircraftDataCapability.Bank));
        Assert.Equal(CapabilityAvailability.Unknown, report.Get(AircraftDataCapability.Gear));
        Assert.Equal(CapabilityAvailability.Unsupported, report.Get(AircraftDataCapability.Engines));
        Assert.Equal(CapabilityAvailability.Unsupported, report.Get(AircraftDataCapability.LandingLight));
    }

    [Fact]
    public void Once_observed_capability_stays_supported_for_same_aircraft()
    {
        var monitor = new AircraftCapabilityMonitor();

        var first = monitor.Observe(XPlaneLike, Snapshot(title: "Same Aircraft", bank: 18));
        var second = monitor.Observe(XPlaneLike, Snapshot(title: "Same Aircraft", bank: null));

        Assert.Equal(CapabilityAvailability.Supported, first.Get(AircraftDataCapability.Bank));
        Assert.Equal(CapabilityAvailability.Supported, second.Get(AircraftDataCapability.Bank));
    }

    [Fact]
    public void Aircraft_identity_change_resets_observed_capabilities()
    {
        var monitor = new AircraftCapabilityMonitor();

        monitor.Observe(XPlaneLike, Snapshot(title: "Aircraft One", bank: 15));
        var second = monitor.Observe(XPlaneLike, Snapshot(title: "Aircraft Two", bank: null));

        Assert.Equal(CapabilityAvailability.Unknown, second.Get(AircraftDataCapability.Bank));
    }

    [Fact]
    public void Fenix_title_selects_named_adapter_without_assuming_capabilities()
    {
        var monitor = new AircraftCapabilityMonitor();
        var descriptor = XPlaneLike with {
            Kind = SimulatorKind.MicrosoftFlightSimulator,
            DisplayName = "MSFS test",
            ConnectorId = "simconnect-test",
            Capabilities = XPlaneLike.Capabilities | SimulatorCapabilities.Engines | SimulatorCapabilities.Lights
        };

        var report = monitor.Observe(descriptor, Snapshot(
            title: "Fenix Simulations A320 CFM",
            bank: null,
            gear: null));

        Assert.Equal("fenix-a320", report.AdapterId);
        Assert.Equal("Fenix A320", report.AdapterName);
        Assert.Equal(CapabilityAvailability.Unknown, report.Get(AircraftDataCapability.Bank));
        Assert.Equal(CapabilityAvailability.Unknown, report.Get(AircraftDataCapability.Gear));
    }

    [Fact]
    public void Adapter_normalization_is_in_the_data_path()
    {
        var registry = new AircraftAdapterRegistry([new AbsoluteBankAdapter(), new GenericAircraftAdapter()]);
        var monitor = new AircraftCapabilityMonitor(registry);

        var result = monitor.AdaptAndObserve(XPlaneLike, Snapshot(title: "Vendor aircraft", bank: -23));

        Assert.Equal(23, result.Snapshot.BankDegrees);
        Assert.Equal("test-normalizer", result.Report.AdapterId);
        Assert.Equal(CapabilityAvailability.Supported, result.Report.Get(AircraftDataCapability.Bank));
    }

    [Fact]
    public void Capability_enums_are_serialized_as_readable_strings_for_webview()
    {
        var monitor = new AircraftCapabilityMonitor();
        var report = monitor.Observe(XPlaneLike, Snapshot(title: "JSON Aircraft", bank: null));
        var json = JsonSerializer.Serialize(report);

        Assert.Contains("\"Bank\"", json);
        Assert.Contains("\"Unknown\"", json);
        Assert.DoesNotContain("\"Capability\":8", json);
    }

    private static AircraftSnapshot Snapshot(
        string? title = null,
        double? bank = 0,
        double? fuel = 8000,
        bool? gear = true) =>
        new(
            Guid.NewGuid(),
            DateTimeOffset.UtcNow,
            Latitude: 48.7,
            Longitude: 2.3,
            AltitudeMslFeet: 5000,
            AltitudeAglFeet: 4500,
            IndicatedAirspeedKnots: 210,
            GroundSpeedKnots: 220,
            VerticalSpeedFeetPerMinute: 0,
            HeadingDegrees: 180,
            FuelWeight: fuel,
            OnGround: false,
            ParkingBrake: null,
            GearDown: gear,
            FlapsPercent: null,
            BankDegrees: bank,
            AircraftTitle: title);

    private sealed class AbsoluteBankAdapter : IAircraftAdapter
    {
        public string Id => "test-normalizer";
        public string DisplayName => "Test normalizer";
        public int Priority => 500;
        public bool Matches(AircraftSnapshot snapshot) => true;

        public AircraftSnapshot Normalize(AircraftSnapshot snapshot) =>
            snapshot with {
                BankDegrees = snapshot.BankDegrees is { } bank ? Math.Abs(bank) : null
            };
    }
}
