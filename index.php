<?php

/*
    Copyright 2021 Marcin Kowol
    Paczer jest rozpowszechniany na warunkach Powszechnej Licencji Publicznej GNU 

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>

    Niniejszy program jest wolnym oprogramowaniem; możesz go
    rozprowadzać dalej i/lub modyfikować na warunkach Powszechnej
    Licencji Publicznej GNU, wydanej przez Fundację Wolnego
    Oprogramowania - według wersji 3 tej Licencji lub (według twojego
    wyboru) którejś z późniejszych wersji.

    Niniejszy program rozpowszechniany jest z nadzieją, iż będzie on
    użyteczny - jednak BEZ JAKIEJKOLWIEK GWARANCJI, nawet domyślnej
    gwarancji PRZYDATNOŚCI HANDLOWEJ albo PRZYDATNOŚCI DO OKREŚLONYCH
    ZASTOSOWAŃ. W celu uzyskania bliższych informacji sięgnij do
    Powszechnej Licencji Publicznej GNU.

    Z pewnością wraz z niniejszym programem otrzymałeś też egzemplarz
    Powszechnej Licencji Publicznej GNU (GNU General Public License);
    jeśli nie - zobacz <http://www.gnu.org/licenses/>.

*/

// Load the Google API PHP Client Library.
require_once '/home/vendor/autoload.php'; // w tej linii należy podać WŁASNY adres biblioteki pobranej stąd https://github.com/googleapis/google-api-php-client
// logowanie

session_start();
// session_unset();

$client = new Google_Client();
$client->setAuthConfig(__DIR__ . '/client_secrets.json');
$client->addScope(Google_Service_Analytics::ANALYTICS_READONLY);

// If the user has already authorized this app then get an access token
// else redirect to ask the user to authorize access to Google Analytics.
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  
  // Set the access token on the client.
  $client->setAccessToken($_SESSION['access_token']);

  // Create an authorized analytics service object.
  $analytics = new Google_Service_AnalyticsReporting($client);

  if (isset($_POST['cel'])) {
    $start = $_POST['cel'];
  } else {
    $start = '';
  }
  if (isset($_POST['lp'])) {
    $stop = $_POST['lp'];
  } else {
    $stop = '';
  }
  if (isset($_POST['viewid'])) {
    $VIEW_ID = $_POST['viewid'];
  }	else {
    $VIEW_ID = '';
  }
  if (isset($_POST['d1'])) {
    $data1 = $_POST['d1'];
  }	else {
    $data1 = "7daysAgo";
  }
  if (isset($_POST['d2'])) {
    $data2 = $_POST['d2'];
  }	else {
    $data2 = "yesterday";
  }
  if (isset($_POST['kroki'])) {
    $maxkroki = $_POST['kroki'];
  }	else {
    $maxkroki = 10;
  }
  if (isset($_POST['galezie'])) {
    $maxgalezi = $_POST['galezie'];
  }	else {
    $maxgalezi = 3;
  }
  if (isset($_POST['procent'])) {
    $procent = $_POST['procent'];
  }	else {
    $procent = 6;
  }

  if ((isset($VIEW_ID)) && ($VIEW_ID<>"")) {
    // Call the Analytics Reporting API V4.
    $response = getReport($analytics,$VIEW_ID,$data1,$data2);
    $wszystko = pobraneDane($response);
  }

  echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">
  
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Paczer v 4.0 beta</title>
    <meta name="robots" content="none" />
    <meta name="googlebot" content="none" />
    <meta name="author" content="Marcin Kowol" />

    <!-- Resources -->
    <script src="https://cdn.amcharts.com/core.js"></script>
    <script src="https://cdn.amcharts.com/amcharts4/charts.js"></script>
    <script src="https://cdn.amcharts.com/amcharts4/themes/animated.js"></script>
    <script src="https://cdn.amcharts.com/amcharts4/lang/pl_PL.js"></script>

  </head>
  <body>
  <form enctype="application/x-www-form-urlencoded" action="index.php" accept-charset="utf-8" method="POST">
  <div class="row1">
    <span class="label" style="display:inline;"><strong>ustawienia niezbędne</strong></span>
    <span class="label"> widok Google Analytics (123456789): </span>
    <span class="formw"><input type="text" name="viewid" placeholder="' . $VIEW_ID . '" value="' . $VIEW_ID . '"/></span>
    <span class="label"> | data początkowa: </span>
    <span class="formw"><select name="d1">
      <option';
      if ($data1 == "yesterday") { echo ' selected'; }
      echo '>yesterday</option>
      <option';
      if ($data1 == "7daysAgo") { echo ' selected'; }
      echo '>7daysAgo</option>
      <option';
      if ($data1 == "31daysAgo") { echo ' selected'; }
      echo '>31daysAgo</option>
    </select></span>
    <span class="label"> | data końcowa: </span>
    <span class="formw"><select name="d2">
    <option';
    if ($data2 == "today") { echo ' selected'; }
    echo '>today</option>
    <option';
    if ($data2 == "yesterday") { echo ' selected'; }
    echo '>yesterday</option>
    </select></span>
  </div>';
  if ($VIEW_ID<>'') {
  echo '<div class="row1">
  <span class="label">adres strony celu: </span>
  <span class="formw"><select name="cel">';
  foreach (wszystkieStrony($wszystko) as $value) {
    echo '<option value="'. $value . '"';
    if ($value == $start) { echo ' selected';}
    echo '>' . $value . ' -> ' . liczbaUserow($value,$wszystko) . '</option>';
  };
  echo '</select></span>
  </div>
  <div class="row1">
  <span class="label" style="display:inline;"><strong>ustawienia opcjonalne</strong></span>
  <span class="label"> początek ścieżki inny niż strona główna: </span>
  <span class="formw"><select name="lp">';
  foreach (wszystkieStrony($wszystko) as $value) {
    echo '<option';
    if ($value == $stop) { echo ' selected';}
    echo '>' . $value . '</option>';
  };
  echo '</select></span>
  </div>
  <div class="row1">
  <span class="label">maksymalna długość ścieżki: </span>
  <span class="formw"><select name="kroki">';
  for ($i=3;$i<16;$i++) {
    echo '<option value="'. $i . '"';
    if ($i == $maxkroki) { echo ' selected';}
    echo '>' . $i . '</option>';
  };
  echo '</select></span>
  <span class="label"> | maksymalna liczba stron prowadzących do każdej strony: </span>
  <span class="formw"><select name="galezie">';
  for ($i=1;$i<11;$i++) {
    echo '<option value="'. $i . '"';
    if ($i == $maxgalezi) { echo ' selected';}
    echo '>' . $i . '</option>';
  };
  echo '</select></span>
  <span class="label"> | minimalny procent ruchu dla uwzględnienia strony z ruchem przychodzącym: </span>
  <span class="formw"><select name="procent">';
  for ($i=1;$i<26;$i++) {
    echo '<option value="'. $i . '"';
    if ($i == $procent) { echo ' selected';}
    echo '>' . $i . '%</option>';
  };
  echo '</select></span>
  </div>';
  }
  echo '
  <div class="row1">
    <input type="hidden" name="kontrolka" value="wypelniony" />
    <input type="submit" value="';
    if ($VIEW_ID<>'') {
      echo 'wizualizuj ścieżkę';
    } else {
      echo 'załaduj dane z Google Analytics';
    }
  echo '" />
  </div>
  </form>';

  if (($VIEW_ID<>'') && (isset($start))) { // url celu
    $krok=$start;
    $licznik_krokow=0;
    $polaczenia[$licznik_krokow] = krok($krok); // pierwszy krok
    $licznik_krokow++;
    $temp = krokTylkoStrony($krok);
    $ok = 1;
    while ( $ok && ($licznik_krokow<$maxkroki) ) {
      $polaczenia[$licznik_krokow] = [];
      $temp2 = [];
      foreach ($temp as $b) {
        if ( !((substr_count($b,'/') == 1) && (substr($b, 0, -1) == '/')) && ($b<>$stop) ) {
          $polaczenia[$licznik_krokow] = dodajTablice($polaczenia[$licznik_krokow],krok($b));
          $temp2 = dodajTablice($temp2, krokTylkoStrony($b));
        } else {
          $ok = 0;
        }
      }
      $temp=$temp2;
      $licznik_krokow++;
    }
    $licznik_krokow--;

    $polaczenia=sprzatajTablice($polaczenia);
    $polaczenia=array_reverse($polaczenia);
    prezentacja($polaczenia, stronyWyjscia());
  }

} else {
  $redirect_uri = 'https://paczer.biurko24.pl/oauth2callback.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}

// przygotowanie danych zapytania
function getReport($analytics,$VIEW_ID,$data1,$data2) {

  // Create the DateRange object.
  $dateRange = new Google_Service_AnalyticsReporting_DateRange();
  $dateRange->setStartDate($data1);
  $dateRange->setEndDate($data2);

  // Create the Metrics object.
  $users = new Google_Service_AnalyticsReporting_Metric();
  $users->setExpression("ga:users");
  $users->setAlias("uzytkownicy");

  //Create the Dimensions object.
  $pagePath = new Google_Service_AnalyticsReporting_Dimension();
  $pagePath->setName("ga:pagePath");
  $previousPagePath = new Google_Service_AnalyticsReporting_Dimension();
  $previousPagePath->setName("ga:previousPagePath");

  $ordering = new Google_Service_AnalyticsReporting_OrderBy();
  $ordering->setFieldName("ga:users");
  $ordering->setOrderType("VALUE");   
  $ordering->setSortOrder("DESCENDING");

  // Create the ReportRequest object.

  $request = new Google_Service_AnalyticsReporting_ReportRequest();
  $request->setViewId($VIEW_ID);
  $request->setDateRanges($dateRange);
  $request->setDimensions(array($pagePath,$previousPagePath));
  $request->setMetrics(array($users));
  $request->setPageSize(10000);
  $request->setOrderBys($ordering);

  $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
  $body->setReportRequests( array( $request) );
  return $analytics->reports->batchGet( $body );

}

function pobraneDane($reports) {
  for ( $reportIndex = 0; $reportIndex < count( $reports ); $reportIndex++ ) {
    $report = $reports[ $reportIndex ];
    $header = $report->getColumnHeader();
    $dimensionHeaders = $header->getDimensions();
    $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
    $rows = $report->getData()->getRows();

    for ( $rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
      $row = $rows[ $rowIndex ];
      $dimensions = $row->getDimensions();
      $metrics = $row->getMetrics();
      if (strlen($dimensions[0])>1) {
        $dane[$rowIndex]['dokad'] = substr($dimensions[0],strpos($dimensions[0],'/'));
      } else {
        $dane[$rowIndex]['dokad'] = $dimensions[0];
      }
      if (strlen($dimensions[1])>1) {
        $dane[$rowIndex]['skad'] = substr($dimensions[1],strpos($dimensions[1],'/'));
      } else {
        $dane[$rowIndex]['skad'] = $dimensions[1];
      }
      for ($j = 0; $j < count($metrics); $j++) {
        $values = $metrics[$j]->getValues();
        for ($k = 0; $k < count($values); $k++) {
          $entry = $metricHeaders[$k];
          $dane[$rowIndex]['ile'] = $values[$k];
        }
      }
    }
  }
  return($dane);
}

function krok($strona) { // zwraca tablicę stron prowadzących do danej strony z liczbą
  global $maxgalezi,$procent;
  $kroki = [];
  $u=0;
  $i=0;
  $licznik_stron=0;
  $limit=liczbaUserow($strona);
  while (($u<$limit) && ($i<$maxgalezi)) {
    if ( ($strona<>znajdzPoprzedni($strona)[$licznik_stron]['skad']) && (znajdzPoprzedni($strona)[$licznik_stron]['ile']*(100/$procent)>liczbaUserow($strona)) ) { // jeśli poprzednia strona nie jest taka sama (odświeżenie strony) oraz przyszło z niej więcej niz % użytkowników
      $kroki[$i]['dokad']=$strona; // url ostatniego kroku
      $kroki[$i]['skad']=znajdzPoprzedni($strona)[$licznik_stron]['skad'];
      $kroki[$i++]['ile']=znajdzPoprzedni($strona)[$licznik_stron]['ile'];
    }
    $u+=znajdzPoprzedni($strona)[$licznik_stron++]['ile'];
  }
  return($kroki);
}

function krokTylkoStrony($strona) {
  $polaczenia = [];
  $liczydlo=0;
  $temp = krok($strona);
  for ($a=0;$a<count($temp);$a++) {
    $polaczenia[$liczydlo++]=$temp[$a]['skad'];
  }
  return($polaczenia);
}

function stronyWyjscia() {
  global $wszystko;
  $i=0;
  foreach ($wszystko as $w) {
    $robocza[$w['skad']][$i]['dokad'] = $w['dokad'];
    $robocza[$w['skad']][$i]['ile'] = $w['ile'];
//    $robocza[$w['skad']][$i]['strona'] = $w['dokad'];
    $i++;
  }
  asort($robocza);
  return($robocza);
}

function dodajTablice($tab1,$tab2) {
  if (!($tab1)) {
    $tab3 = $tab2;
  } elseif (!($tab2)) {
    $tab3 = $tab1;
  } else {
    for ($b=0;$b<count($tab1);$b++) {
      $tab3[] = $tab1[$b];
    }
    for ($b=0;$b<count($tab2);$b++) {
      $tab3[] = $tab2[$b];
    }
  }
  return($tab3);
}

function sprzatajTablice($tab) {
  for ($j=0; $j<=count($tab); $j++) {
    $i=0;
    while (isset($tab[$j][$i]['dokad'])) {
      $a1=$tab[$j][$i]['skad'];
      $a2=$tab[$j][$i]['dokad'];
      $a3=$tab[$j][$i]['ile'];
      if (($a1=='')&&($a2=='')&&($a3=='')) {
        unset($tab[$j][$i]);
      } else {
        for ($k=0; $k<=count($tab); $k++) {
          $l=0;
          while (isset($tab[$k][$l]['dokad'])) {
            if ( ($j<>$k) && ($i<>$l) && ($a1==$tab[$k][$l]['skad']) && ($a2==$tab[$k][$l]['dokad']) && ($a3==$tab[$k][$l]['ile']) ) {
              unset($tab[$k][$l]);
            }
            $l++;
          }
        }
      }
      $i++;
    }
  }
  $tab=array_filter($tab);
  return($tab);
}

// znalezienie poprzednich stron
function znajdzPoprzedni($cel) {
  global $wszystko;
  $i=0;
  foreach ($wszystko as $value) {
    if ($value['dokad'] == $cel) {
      $robocza[$i]['ile'] = $value['ile'];
      $robocza[$i]['skad'] = $value['skad'];
      $i++;
    }
  }
  asort($robocza);
  return($robocza);
}

// lista wszystkich stron (co najmniej 2 użytkowników)
function wszystkieStrony($wszystko) {
  foreach ($wszystko as $value) {
    if ($value['ile']>1) {
      $robocza[] = $value['dokad'];
    }
  }
  $robocza = array_unique($robocza);
  sort($robocza);
  return($robocza);
}

// całkowita liczba userów dla strony
function liczbaUserow($cel) {
  global $wszystko;
  $zwrotka = 0;
  foreach ($wszystko as $value) {
    if ($value['dokad'] == $cel) {
      $zwrotka += $value['ile'];
    }
  }
  return($zwrotka);
}

// rysowanie lejka
function prezentacja($lejek,$wyjscia) {
  global $wszystko;
  // print_r($lejek);

  echo '
  <script>
  
  // Themes begin
  am4core.useTheme(am4themes_animated);
  // Themes end

  var chart = am4core.create("chartdiv", am4charts.SankeyDiagram);
  chart.paddingTop = 20;
  chart.paddingLeft = 20;
  chart.paddingBottom = 20;
  chart.orientation = "vertical";
  chart.hiddenState.properties.opacity = 0; // this creates initial fade-in
  chart.interpolationDuration = 0;
  chart.language.locale = am4lang_pl_PL;
  chart.numberFormatter.language = new am4core.Language();
  chart.numberFormatter.language.locale = am4lang_pl_PL;
  chart.dateFormatter.language = new am4core.Language();
  chart.dateFormatter.language.locale = am4lang_pl_PL;
  
  chart.data = [
    ';
    for ($j=0; $j<=count($lejek); $j++) {
      $i=0;
      while (isset($lejek[$j][$i]['dokad'])) {
          echo '{ from: "' . $lejek[$j][$i]['skad']. '", to: "' . $lejek[$j][$i]['dokad']. '", value: ' . $lejek[$j][$i]['ile'] . ', wyjscia: "'; 
            $www = '';
            foreach ($wyjscia[$lejek[$j][$i]['dokad']] as $w) {
              if ($lejek[$j][$i]['dokad']<>$w['dokad']) {
                $www .= $w['dokad'] . ' (' . $w['ile'] . ')' . '\n';
              }
            }      
          echo '[bold]wyjścia:[/]\n' . $www . '" },
        ';
        $i++;
      }
    }
  echo ']

  var hoverState = chart.links.template.states.create("hover");
  // hoverState.properties.fillOpacity = 0.6;  

  // Configure data fields
  chart.dataFields.fromName = "from";
  chart.dataFields.toName = "to";
  chart.dataFields.value = "value";

  var nodeTemplate = chart.nodes.template;
  nodeTemplate.height = 35;
  nodeTemplate.nameLabel.label.fill = am4core.color("#000");
  nodeTemplate.nameLabel.label.fontWeight = "bold";
  nodeTemplate.nameLabel.label.fontFamily = "Arial";
  nodeTemplate.nameLabel.height = undefined;
  nodeTemplate.nameLabel.label.hideOversized = false; 
  nodeTemplate.clickable = false;
  nodeTemplate.tooltipText = "{wyjscia}";

  chart.nodes.template.nameLabel.label.truncate = false;

  // Configure links
  var linkTemplate = chart.links.template;
  linkTemplate.tension = 1;
  linkTemplate.controlPointDistance = 0.3;
  linkTemplate.colorMode = "gradient"; // toNode  
  linkTemplate.fillOpacity = 0;
  linkTemplate.middleLine.strokeOpacity = 0.9;
  linkTemplate.middleLine.strokeWidth = 15;

  </script>

  <div id="chartdiv" style="width:';
  $szerokosc = 0;
  for ($j=0; $j<count($lejek); $j++) {
    if (is_array($lejek[$j])) { $k = count($lejek[$j]); } else { $k = 1; }
    if ($szerokosc<$k) { $szerokosc = $k; }
  }
  echo $szerokosc*150 . 'px;height:' . count($lejek)*200 . 'px;"></div>

  <table border="1">
    <thead>
      <tr>
          <th>ścieżka celu</th>
      </tr>
    </thead>
    <tbody>
    ';
      $kol = 1;
      for ($i=0;$i<count($lejek);$i++) {
        $j=0;
        if (isset($lejek[$i][$j]['dokad'])) {
          echo '<tr><td><strong>krok ' . $kol++ . '</strong></td>';
        }
        while (isset($lejek[$i][$j]['dokad'])) {
          echo '<td>' . $lejek[$i][$j]['skad'] . ' -> ' . $lejek[$i][$j]['dokad'] . ' | ' . $lejek[$i][$j]['ile'];
          if ((liczbaUserow($lejek[$i][$j]['dokad']))!==null) {
            echo ' (' . liczbaUserow($lejek[$i][$j]['dokad']) . ')</td>';
          }
          $j++;
        }
        echo '</tr>';
      }
      echo '
    </tbody>
  </table>

  </body>
  </html>';

}
?>
