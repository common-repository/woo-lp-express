<?php
/**
 * Help tab
 */
/**
 * Don't allow to call this file directly
 **/
if(!defined('ABSPATH')) {
    die;
}
?>
<div class="wrapper" style="display:flex">
    <div class="section" id="parcel-types" style="width:40%">
        <h1>Siuntimo būdai</h1>
        <p>LP Express siūlo keletą siuntimo būdų.
            Skirtumas tarp jų yra kur siuntinys keliaus arba kam bus siunčiamas.
            Šis sąrašas rodo visus galimus siuntimo būdus.</p>
        <ul id="ref-product-eb-id">
            <li><dl class="first docutils">
                    <dt><b>EB</b></dt>
                    <dd><p class="first last">Siuntinį pasiima kurjeris tiesiai iš siuntėjo adreso ir nuveža tiesiai pas gavėją.</p>
                    </dd>
                </dl>
            </li>
        </ul>
        <ul id="ref-product-ab-id">
            <li><dl class="first docutils">
                    <dt><b>AB</b></dt>
                    <dd><p class="first last">Siuntinį pasiima kurjeris tiesiai iš siuntėjo adreso ir pristato į nurodytą Lietuvos pašto skyriu.</p>
                    </dd>
                </dl>
            </li>
        </ul>
        <ul id="ref-product-hc-id">
            <li><dl class="first docutils">
                    <dt><b>HC</b></dt>
                    <dd><p class="first last">Siuntinį pasiima kurjeris tiesiai iš siuntėjo adreso ir nuveža į siuntėjo nurodytą terminalą.
                            Siuntiniui atkeliavus į terminalą, gavėjas yra informuojamas.</p>
                    </dd>
                </dl>
            </li>
        </ul>
        <ul id="ref-product-cc-id">
            <li><dl class="first docutils">
                    <dt><b>CC</b></dt>
                    <dd><p class="first last">Siuntėjas pats nugabena siuntinį į terminalą. Bet siuntinį iš terminalo pasiima kurjeris ir jį nugabeną į siuntėjo nurodytą terminalą.
                        Siuntiniui atkeliavus į terminalą, gavėjas yra informuojamas.</p>
                    </dd>
                </dl>
            </li>
        </ul>
        <ul id="ref-product-ch-id">
            <li><dl class="first docutils">
                    <dt><b>CH</b></dt>
                    <dd><p class="first last">Siuntėjas pats nugabena siuntinį į terminalą. Bet siuntinį iš terminalo pasiima kurjeris ir nugabena tiesiai į gavėjo adresą.</p>
                    </dd>
                </dl>
            </li>
        </ul>
        <ul id="ref-product-ca-id">
            <li><dl class="first docutils">
                    <dt><b>CA</b></dt>
                    <dd><p class="first last">Siuntėjas pats nugabena siuntinį į terminalą. Bet siuntinys perduodamas siuntimui į užsienyje siuntėjo nurodytą adresą.</p>
                    </dd>
                </dl>
            </li>
        </ul>
        <ul id="ref-product-in-id">
            <li><dl class="first docutils">
                    <dt><b>IN</b></dt>
                    <dd><p class="first last">Siuntinys pasiimamas iš adreso kurį nurodė siuntėjas ir perduodamas siuntimui į užsienyje siuntėjo nurodytą adresą.</p>
                    </dd>
                </dl>
            </li>
        </ul>
    </div>
</div>