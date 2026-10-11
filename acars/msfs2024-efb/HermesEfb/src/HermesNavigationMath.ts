/** Great-circle advisory calculations in nautical miles, never an ATC clearance. */
export type GeoPoint={lat:number;lon:number;elevationFt?:number|null};
export const validPoint=(p:GeoPoint|null|undefined):p is GeoPoint=>!!p&&Number.isFinite(p.lat)&&Number.isFinite(p.lon)&&Math.abs(p.lat)<=90&&Math.abs(p.lon)<=180;
const rad=(d:number)=>d*Math.PI/180;
export function greatCircleNm(a:GeoPoint,b:GeoPoint):number{
 const dLat=rad(b.lat-a.lat),dLon=rad(b.lon-a.lon);
 const h=Math.sin(dLat/2)**2+Math.cos(rad(a.lat))*Math.cos(rad(b.lat))*Math.sin(dLon/2)**2;
 return 3440.065*2*Math.asin(Math.sqrt(Math.min(1,Math.max(0,h))));
}
export function todEstimateNm(altitudeFt:number,airportElevationFt:number=0):number|null{
 if(!Number.isFinite(altitudeFt)||!Number.isFinite(airportElevationFt)||altitudeFt<=airportElevationFt+2000)return null;
 // 3° geometric path plus 10 NM allowance is a planning prompt, not a clearance.
 return (altitudeFt-airportElevationFt)/318.4+10;
}
export function estimateArrivalFuel(fuelLb:number,gsKt:number,distanceNm:number,burnLbPerHour:number):number|null{
 if(![fuelLb,gsKt,distanceNm,burnLbPerHour].every(Number.isFinite)||gsKt<80||burnLbPerHour<=0||distanceNm<0)return null;
 return fuelLb-burnLbPerHour*distanceNm/gsKt;
}
/** Course offset is only valid for structured waypoints, not the raw OFP route string. */
export function crossTrackNm(position:GeoPoint,a:GeoPoint,b:GeoPoint):number|null{
 if(![position,a,b].every(validPoint))return null;
 const d13=greatCircleNm(a,position)/3440.065;
 const bearing=(from:GeoPoint,to:GeoPoint)=>{
  const dLon=rad(to.lon-from.lon);
  return Math.atan2(Math.sin(dLon)*Math.cos(rad(to.lat)),Math.cos(rad(from.lat))*Math.sin(rad(to.lat))-Math.sin(rad(from.lat))*Math.cos(rad(to.lat))*Math.cos(dLon));
 };
 return Math.asin(Math.max(-1,Math.min(1,Math.sin(d13)*Math.sin(bearing(a,position)-bearing(a,b)))))*3440.065;
}
