<?
// PHP settings
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

// JSON header
header('Cache-Control: no-cache, must-revalidate');
header('Content-Type: application/json');

// ReST API and routing
$method = $_SERVER['REQUEST_METHOD'];
$request = explode("/", substr(@$_SERVER['PATH_INFO'], 1));

switch ($method) {
  case 'GET':
    rest_get($request);  
    break;
  case 'POST':
    rest_post($request);
    break;
  default:
    rest_error($request);  
    break;
}

function rest_get($request)
{
  if ($request[0] == 'some_var_to_request_data') {
    GetData($request);         
  } else {
    rest_error($request[0]);
  }
  return;
}

function rest_post($request)
{
  if ($request[0] == 'submit') {
    SaveData($request);
  } else {
    rest_error($request[0]);
  }
  return;
}

function rest_error($request)
{
  $msg = Array();
  $msg['status'] = 'Error';
  $msg['reason'] = 'Invalid request!';
  $msg['URI'] = "".$_SERVER["REQUEST_URI"];
  echo json_encode($msg);
  return;
}

// Helper function to echo/log output
function appResponse($status, $reason=NULL) {
  $output = array();
  $output['status'] = $status;
  $output['reason'] = $reason;
  echo json_encode($output);
  return;
}

// MySQL function to create PDO object
function connectDB() {
  sleep(1); // mandatory 1 second pause for all DB requests :)
  $db = new PDO('mysql:host=localhost;dbname=my_database;charset=utf8', 'username', 'password');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // use try/catch to catch DB errors:  catch(PDOException $ex), $ex->getMessage(); 
  $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);     // use REAL/NATIVE prepared statements w/ MySQL
  return $db;
}

// main app functions 

function SaveData($request) {
  // check for missing input.
  if (empty($_POST['data']))  {
    return appResponse('error', 'Please complete all required fields!');
  }
  
  // gather data from POST...
  $survey_data_array = json_decode( $_POST['data'], true ); // true = return array not StdClass
  
  $db = connectDB();
  $stmt = $db->prepare("INSERT INTO table(field1,field2) VALUES(:field1val, :field2val)");
  try {
    $stmt->execute(array(':field1val' => 'foo', ':field2val' => 'bar'));
    return appResponse('ok');
  } catch(PDOException $ex) {
    return appResponse('error', $ex->getMessage());
  }
}

function GetData($request) { 
  $db = connectDB();
  $query_sql = "SELECT field1, field2 from table limit 5";
  $query = $db->query($query_sql);
  $results = $query->fetchAll(PDO::FETCH_ASSOC);
  
  foreach ($results[0] as $key => $value) {
    // do something
  }
  return; // data array here
}
?>
