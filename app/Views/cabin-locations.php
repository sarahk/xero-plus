<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>

<style>
    /*
 * Always set the map height explicitly to define the size of the div element
 * that contains the map.
 */
    #map {

        height: 400px; /* The height is 400 pixels */
        width: 100%;
    }

    /*
     * Optional: Makes the sample page fill the window.
     */
    html,
    body {
        height: 100%;
        margin: 0;
        padding: 0;
    }
</style>
<div class="page-header">
    <div>
        <h1 class="page-title">Cabin Locations</h1>
        <!--        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Forms</a></li>
            <li class="breadcrumb-item active" aria-current="page">Form-Elements</li>
        </ol>-->
    </div>

</div>
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="card">
            <div class="card-body">
                <!--The div element for the map -->
                <div id="map"></div>
            </div>
        </div>
    </div>
</div>
<!-- prettier-ignore -->
<script>(g => {
        var h, a, k, p = "The Google Maps JavaScript API", c = "google", l = "importLibrary", q = "__ib__",
            m = document, b = window;
        b = b[c] || (b[c] = {});
        var d = b.maps || (b.maps = {}), r = new Set, e = new URLSearchParams,
            u = () => h || (h = new Promise(async (f, n) => {
                await (a = m.createElement("script"));
                e.set("libraries", [...r] + "");
                for (k in g) e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]);
                e.set("callback", c + ".maps." + q);
                a.src = `https://maps.${c}apis.com/maps/api/js?` + e;
                d[q] = f;
                a.onerror = () => h = n(Error(p + " could not load."));
                a.nonce = m.querySelector("script[nonce]")?.nonce || "";
                m.head.append(a)
            }));
        d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() => d[l](f, ...n))
    })
    ({key: "AIzaSyB41DRUbKWJHPxaFjMAwdrzWzbVKartNGg", v: "beta"});</script>

<script>
    let map;

    async function initMap() {
        // The location of Uluru
        const position = {lat: -25.344, lng: 131.031};
        // Request needed libraries.
        //@ts-ignore
        const {Map} = await google.maps.importLibrary("maps");
        const {AdvancedMarkerElement} = await google.maps.importLibrary("marker");

        // The map, centered at Uluru
        map = new Map(document.getElementById("map"), {
            zoom: 4,
            center: position,
            mapId: "DEMO_MAP_ID",
        });

        // The marker, positioned at Uluru
        const marker = new AdvancedMarkerElement({
            map: map,
            position: position,
            title: "Uluru",
        });
    }

    initMap();
    //Note: The JavaScript is compiled from the TypeScript
</script>