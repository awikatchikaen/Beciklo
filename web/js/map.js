var epsg4326 = new OpenLayers.Projection("EPSG:4326");
var epgs900913 = new OpenLayers.Projection("EPSG:900913");
var lat = 50.623;
var lon = 3.085;
var zoom = 10;
var map;

OpenLayers.ImgPath = "http://js.mapbox.com/theme/dark/";

function get_osm_url (bounds) {
  var res = this.map.getResolution();
  var x = Math.round ((bounds.left - this.maxExtent.left) / (res * this.tileSize.w));
  var y = Math.round ((this.maxExtent.top - bounds.top) / (res * this.tileSize.h));
  var z = this.map.getZoom();
  var limit = Math.pow(2, z);

  if (y < 0 || y >= limit) {
  return null;
  }else{
  return this.url + z + "/" + x + "/" + y + "." + this.type;
  }
}

function init() {
    map = new OpenLayers.Map ("map", {
	controls:[
	    new OpenLayers.Control.Navigation(),
	    new OpenLayers.Control.Permalink(),
	    new OpenLayers.Control.PanZoomBar(),
	    new OpenLayers.Control.LayerSwitcher(),
	    new OpenLayers.Control.Attribution()],
	maxExtent: new OpenLayers.Bounds(-20037508.34,-20037508.34,20037508.34,20037508.34),
	maxResolution: 156543.0399,
	numZoomLevels: 16,
	units: 'm',
	projection: epgs900913,
	displayProjection: epsg4326
    } );

    var newLayer = new OpenLayers.Layer.OSM("Beciklo", "http://95.142.175.12/beciklo/map/beciklo/${z}/${x}/${y}.png");
    map.addLayer(newLayer);

    var mapquest = new OpenLayers.Layer.OSM("MapQuest", "http://otile1.mqcdn.com/tiles/1.0.0/osm/${z}/${x}/${y}.png");
    map.addLayer(mapquest);

    var l = new OpenLayers.Layer.TMS( 
    "Mapnik", 
    ["http://tile.openstreetmap.org/"],
    {type:'png',getURL: get_osm_url,transitionEffect: 'resize', displayOutsideMaxExtent: true }, {'buffer':1} );
    map.addLayer(l);

    var pistes = new OpenLayers.Layer.OSM("Pistes", "http://95.142.175.12/beciklo/map/pistes/${z}/${x}/${y}.png");
    pistes.setIsBaseLayer(false);
    pistes.setVisibility(true);
    map.addLayer(pistes);


    if (!map.getCenter()) {
      var lonLat = new OpenLayers.LonLat(lon, lat).transform(epsg4326, map.getProjectionObject());
      map.setCenter(lonLat, zoom);
    }
}