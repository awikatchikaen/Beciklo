<html>
<head>
<title>BeCiklo</title>
<link rel="icon" type="image/png" href="beciklo.png" />
<script src="libs/jquery.js"></script>
<script src="libs/OpenLayers.js"></script>
<script src="libs/links.js"></script>
<script src="http://widgets.digg.com/buttons.js"></script>
<script src="http://www.openstreetmap.org/openlayers/OpenStreetMap.js"></script>
<script src='http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAA7lWHqEJ8J0dH_nO2DXiRkBR-OTfSsytpaYHnDwPMDDb4jKktfBTIiUemA59dfZvN-WlOY6zcCbsJHg'></script>

<script type="text/javascript">
var epsg4326 = new OpenLayers.Projection("EPSG:4326");
var epgs900913 = new OpenLayers.Projection("EPSG:900913");
var lat = 48.86;
var lon = 2.33;
var zoom = 11;
var map;
var gsat;
var linksOuvert=0;

function rediriger(url){
  var tags=" #osm #velo";
  var url = url+$.URLEncode($('#shortlinkanchor')[0].href)+tags; //"http://awikatchikaen.info/test.html?zoom="+zoom+"&lat="+map.getCenter().lat+"&lon="+map.getCenter().lon;
  
  window.open(url);
  //$(location).attr({target :'_blank', href: url});
}

function openLinks(){
  $('#contentLinks').fadeIn("fast");
  $('#links').animate({width: 240,height: 240});
  linksOuvert=1;
}

function fermeLinks(){
  $('#contentLinks').fadeOut("fast");
  $('#links').animate({width: 40,height: 40});
  linksOuvert=0;
}

function toggleLinks(){
  if(linksOuvert==1){fermeLinks();}
  else if(linksOuvert==0){openLinks();}
}

function closeLegende(){
  $("#legende").fadeOut("slow");
}

function openLegende(){
  $("#legende").fadeIn("slow");
}

function toggleDiv(div){
  $("#"+div).slideToggle("slow");
}

function toggleGsat(){	
  toggleLayer(gsat);
}

function toggleLayer(overlay){
  if(overlay.getVisibility()){
    overlay.setVisibility(false);
  }else{
    overlay.setVisibility(true);
  }
}

function getMapLayers() {
  var layerConfig = "";
  
  for (var layers = map.getLayersBy("isBaseLayer", true), i = 0; i < layers.length; i++) {
    layerConfig += layers[i] == map.baseLayer ? "B" : "0";
  }
  
  for (var layers = map.getLayersBy("isBaseLayer", false), i = 0; i < layers.length; i++) {
    layerConfig += layers[i].getVisibility() ? "T" : "F";
  }
  
  return layerConfig;
}


function updateLocation() {
  var layers = getMapLayers();
  
  var mapPosition = OpenLayers.Projection.transform(
    { x: map.getCenter().lon, y: map.getCenter().lat }, 
						    map.getProjectionObject(), 
						    map.displayProjection );
  
  updatelinks( mapPosition.x, mapPosition.y, map.getZoom(), layers);
  
  //expiry.setYear(expiry.getFullYear() + 10); 
  //document.cookie = "_osm_location=" + lonlat.lon + "|" + lonlat.lat + "|" + zoom + "|" + layers + "; expires=" + expiry.toGMTString();
}

function getTileURL(bounds) {
  var res = this.map.getResolution();
  var x = Math.round((bounds.left - this.maxExtent.left) / (res * this.tileSize.w));
  var y = Math.round((this.maxExtent.top - bounds.top) / (res * this.tileSize.h));
  var z = this.map.getZoom();
  var limit = Math.pow(2, z);
  
  if (y < 0 || y >= limit)
  {
    return null;
  }else{
    x = ((x % limit) + limit) % limit;
    
    var url = this.url;
    var path = z + "/" + x + "/" + y + ".png";
    
    if (url instanceof Array) {
      url = this.selectUrl(path, url);
    }
    return url + path;
    
  }
}


function get_osm_url (bounds) {
  var res = this.map.getResolution();
  var x = Math.round ((bounds.left - this.maxExtent.left) / (res * this.tileSize.w));
  var y = Math.round ((this.maxExtent.top - bounds.top) / (res * this.tileSize.h));
  var z = this.map.getZoom();
  var limit = Math.pow(2, z);
  
  if (y < 0 || y >= limit) {
    return null;
  }else {
    // x = ((x % limit) + limit) % limit;
    return this.url + z + "/" + x + "/" + y + "." + this.type;
  }
}

function init() {
  
  $("#contentInfos").slideToggle("fast");
  $("#contentSearch").slideToggle("fast");
  $("#contentOptions").slideToggle("fast");
  
  map = new OpenLayers.Map ("map", {
    controls:[
    new OpenLayers.Control.Navigation(),
			    new OpenLayers.Control.PanZoom(),
			    new OpenLayers.Control.Permalink(),
			    new OpenLayers.Control.LayerSwitcher(),
			    new OpenLayers.Control.Attribution()],
			    maxExtent: new OpenLayers.Bounds(-20037508.34,-20037508.34,20037508.34,20037508.34),
			    maxResolution: 156543.0399,
			    numZoomLevels: 17,
			    units: 'm',
			    projection: epgs900913,
			    displayProjection: epsg4326
  } );
  
  
  var osm_org = new OpenLayers.Layer.OSM();
  
  
  
  
  
  
  var fond = osm_org.clone();
  fond.setName("Fond");
  fond.setUrl("http://tile.tcweb.org/beciklo/${z}/${x}/${y}.png");
  fond.setIsBaseLayer(true);
  fond.setVisibility(true);
  map.addLayer(fond);
  
  var vide = new OpenLayers.Layer.OSM("Vide", "/tiles", {numZoomLevels: 17});
  map.addLayer(vide);
  
  var cycle = new OpenLayers.Layer.TMS(
    "OSM Cycle Map", 
    ["http://a.andy.sandbox.cloudmade.com/tiles/cycle/","http://b.andy.sandbox.cloudmade.com/tiles/cycle/","http://c.andy.sandbox.cloudmade.com/tiles/cycle/"],
    { type: 'png', getURL: getTileURL, displayOutsideMaxExtent: true, transitionEffect: 'resize'});
  map.addLayer(cycle);
  
  var l = new OpenLayers.Layer.TMS( 
  "Mapnik", 
  ["http://tile.openstreetmap.org/"],
  {type:'png',getURL: get_osm_url,transitionEffect: 'resize', displayOutsideMaxExtent: true }, {'buffer':1} );
  map.addLayer(l);
  
  gsat = new OpenLayers.Layer.Google( 
  "Google Satellite", 
  { type: G_SATELLITE_MAP, 'sphericalMercator': true, attribution: '<a href="http://www.google.com/intl/en_ALL/help/terms_maps.html">Terms of Use</a> for <a href="http://maps.google.com/">Google Maps</a>.',numZoomLevels:17 } );
  map.addLayer(gsat);
  
  var infos = osm_org.clone();
  infos.setName("Infos");
  infos.setUrl("http://tile.tcweb.org/beciklo_infos/${z}/${x}/${y}.png");
  infos.setIsBaseLayer(false);
  infos.setVisibility(true);
  
  
  map.addLayer(infos);
  
  
  var pistes = osm_org.clone();
  pistes.setName("BeCiklo");
  pistes.setUrl("http://tile.tcweb.org/beciklo_pistes/${z}/${x}/${y}.png");
  pistes.setIsBaseLayer(false);
  pistes.setVisibility(true);
  map.addLayer(pistes);
  
  var hill = new OpenLayers.Layer.TMS(
    "Hillshading (NASA SRTM3 v2)",
				      ["http://toolserver.org/~cmarqu/hill/"],
				      {type: 'png',  transparent:true,getURL: get_osm_url, displayOutsideMaxExtent: true, isBaseLayer: false,    transparent: true, visibility: false} );
  map.addLayer(hill);
  
  
  /*var hill = new OpenLayers.Layer.TMS(
   *			"Hillshading (NASA SRTM3 v2)",
   *			["http://toolserver.org/~cmarqu/hill/"],
   *			{type: 'png', transparent:true, getURL: get_osm_url,displayOutsideMaxExtent: true, isBaseLayer: false,    transparent: true, visibility: false} );*/
  map.addLayer(hill);
  
  if (!map.getCenter()) {
    var lonLat = new OpenLayers.LonLat(lon, lat).transform(epsg4326, map.getProjectionObject());
    map.setCenter(lonLat, zoom);
  }
  
  fermeLinks();
  map.events.register("moveend", map, updateLocation);
  map.events.register("changelayer", map, updateLocation);
  updateLocation();
  
  }
  
  
  </script>
  <style>
  
  *{
    margin:0;
    padding:0;}
    
    #map{
      width:100%; 
      height:100%; 
      margin:0;
      padding:0;
      position:absolute;
      background-color:silver;
      z-index:100;
    }
    
    div.controlOverlay{
      width:110%;
      height:5%;
      border:1px solid white;
      -moz-border-radius-topleft:20px;
      -moz-border-radius-bottomleft:20px;
      text-align:center;
      position:relative;
      left:80%;
      background-color:#639ace;
    }
    
    div.controlOverlay:hover{
      cursor:pointer;
      left:0%;
    }
    
    
    div.controlOverlaySelected{
      width:110%;
      height:5%;
      border:1px solid white;
      -moz-border-radius-topleft:20px;
      -moz-border-radius-bottomleft:20px;
      text-align:center;
      background-color:#003063;
      color:white;
    }
    
    .menus{
      color:white;
      opacity:0.5;
      padding:5px;
      -moz-border-radius:0 0 10 10;
      -webkit-border-radius:0 0 10 10;
      border:1px solid #B0C4DE;
      background-color:black;
      z-index:10001;
      position:absolute;
      font-size:0.8em;
    }
    
    .menus .title{
      width:100%;
      text-align:center;
      cursor:pointer;
      height:20px;
      font-weight:bold;
      font-size:1.0em;
    }
    
    #legende{
      float:left;
      cursor:pointer;
      width:350px;
      height:250px;
      top:100px;
      left:75px;
      -moz-border-radius:10px;
      -webkit-border-radius:10px;
    }
    
    #infos{
      width:300px;
      right:100px;
      border-top-width:0px;
    }
    
    #search{
      width:150px;
      right:420px;
      border-top-width:0px;
    }
    
    #options{
      width:80px;
      right:590px;
      border-top-width:0px;
    }
    
    #links{
      float:right;
      width:40px;
      height:40px;
      bottom : 0px;
      right:0px;
      border-top-width:1px;
      border-left-width:1px;
      -moz-border-radius:240 10 10 10;
      -webkit-border-radius:240 10 10 10;
      
    }
    
    </style>
    </head>
    
    <body onload="init();">
    <div id="map">
    <div class="menus" id="legende" onclick="javascript:closeLegende();">
    <span style="width:100%">Legende</span>
    <ul style="position:relative; left:15px">
    <li><img src="img/legende/bandesCyclables.png"/> : Bandes cyclables sur voies </li>
    <li><img src="img/legende/pistesCyclables.png"/> : Chemins avec revetement (peut etre ouvert aux pietons)</li>
    <li><img src="img/legende/paths.png"/> : Chemins sans revetement (ouvert aux pietons)</li>
    <li><img src="img/legende/tracks.png"/> : Routes sans revetement </li>
    <li><img src="img/legende/footways.png"/> : Chemins pietons </li>
    <li><img src="img/legende/shop_bicycle.png" style="width:20px"/> : Magasin cycliste</li>
    <li><img src="img/legende/rental_bicycle.png" style="width:20px"/> : Location Vélo</li>
    <li><img src="img/legende/parking.png" style="width:20px"/> : Parking a velo</li>
    <li><img src="img/legende/Drop.png" style="width:20px"/> : Eau potable</li>
    </ul>
    </div>
    
    <div class="menus" id="infos">
    <div id="contentInfos" style="height:500px;">
    <span>Le site BeCiklo &agrave; pour but de fournir une carte centr&eacute;e autour du v&eacute;lo. <br/> Bas&eacute; sur les donn&eacute;es extraites de la base g&eacute;ographique libre OpenStreetMap, BeCiklo a une volont&eacute; d'ouverture.</span>
    <ul style="position:relative; left:15px">
    <li>Maj donnees OSM :  <?$tabfich=file("http://82.228.212.80/map/updateBd.txt"); echo $tabfich[0];?></li> 
    <li>Maj fond : <?$tabfich=file("http://82.228.212.80/map/layers/fond/update.txt"); echo $tabfich[0];?></li>
    <li>Maj BeCiklo : <?$tabfich=file("http://82.228.212.80/map/layers/pistesCyclable/update.txt"); echo $tabfich[0];?></li>
    </ul>
    <a href="javascript:openLegende();" title="legende" style="color:white">Afficher la legende</a>	<br/><br/><br/>
    <ul style="position:relative; left:15px">
    <li>Projet encore en phase de tests </li> 
    <li>Pour participer : <a href="http://wiki.openstreetmap.org/wiki/FR:BeCiklo" style="color:white">Sur le wiki</a></li>
    </ul>
    
    <div > Licence : Lorem Ipsum v42 </div> 
    </div>
    <div class="title" onclick="javascript:toggleDiv('contentInfos');"> Informations </div> 
    </div>
    
    <div class="menus" id="search">
    <div id="contentSearch" style="height:80px;">
    <textarea style="width:100%"></textarea>
    </div>
    <div class="title" onClick="toggleDiv('contentSearch');"> Rechercher un lieu </div> 
    </div>
    
    <div class="menus" id="options">
    <div id="contentOptions" style="height:80px;">
    <ul style="position:relative; left:15px">
    <li><a href="javascript:toggleGsat();">Option 1</a></li>
    <li>Option 2</li>
    <li>Option 3</li>
    </ul>
    </div>
    <div class="title"  onclick="javascript:toggleDiv('contentOptions');"> Options </div> 
    </div>
    
    
    <div class="menus" id="links" onClick="javascript:toggleLinks()">
    <div id="contentLinks" style="border:2px solid #000;width:100%;height:100%;-moz-border-radius-topleft:240;" >
    
    <a rel="nofollow" href="javascript:rediriger('http://twitter.com/home/?status=Great Map :')"  style="opacity:1;position:absolute;right: 15px; top: 15px;"><img src="img/twitter.png" alt="Partager sur Twitter"/></a>
    <a rel="nofollow" href="javascript:rediriger('http://identi.ca//index.php?action=newnotice&status_textarea=Great Map :')" style="opacity:1;position:absolute;right: 75px; top: 45px;"> <img src="img/identica.jpg" alt="Partager sur Identi.ca" style="width:36px"/></a> 
    <div style="position:absolute;right:120px;top:88px;">
    <a class="DiggThisButton DiggMedium" href="http://digg.com/submit?url=http%3A//www.awikatchikaen.info/" ></a>
    </div>
    <div style="position:absolute;right:180px;top:175px;z-index:10010;">
    <a title="Partager sur Google Buzz" href="javascript:rediriger('http://www.google.com/buzz/post?message=Great%20Map%20:&url=')"><img src="img/buzz.png" alt="Partager ceci sur Google Buzz" style="width:36px"/></a>
    </div>
    
    <a href="test.html" id="shortlinkanchor" style="position:absolute;right:5px;bottom:20px;color:white;">Lien court</a>    
    <a href="test.html" id="permalinkanchor" style="position:absolute;right:5px;bottom:5px;color:white;">Lien permanent</a>    
    
    </div>
    </div>
    
    
    <!--a href="test.html" id="shortlinkanchor" style="z-index:10001;position:absolute;float:right; bottom:0px;right:0px;" >Lien Court</a-->    
    </div>
    
    </body>
    
    </html>
    
    