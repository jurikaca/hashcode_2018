<?php

/**
 * Methods to interact with file, read parameters from file, write output/result on destination file.
 * Class FileReader
 */
class FileReader {
    public $file_name; // current file name

    function __construct($file_name) {
        $this->file_name = $file_name;
    }

    public function readFile()
    {
        $myfile = fopen('task_files/'.$this->file_name, "r") or die("Unable to open file!");

        $output = $this->readFirstLine($myfile); // read the first row from the file

        $output['rides'] = $this->readOtherLines($myfile); // read other lines of file

        fclose($myfile);

        return $output;
    }

    /**
     * read first row from the file
     *
     * @param $myfile, file object
     */
    private function readFirstLine($myfile){
        $first_line = fgets($myfile);
        $first_line = explode(' ', $first_line);

        return [
            'R' => $first_line[0],
            'C' => $first_line[1],
            'F' => $first_line[2],
            'N' => $first_line[3],
            'B' => $first_line[4],
            'T' => $first_line[5],
        ];
    }

    /**
     * read all other lines except first line, collecting data needed for calculation
     *
     * @param $myfile
     */
    private function readOtherLines($myfile){
        $rides = [];
        while(!feof($myfile)) {
            $current_line = fgets($myfile);
            if ($current_line != "") {
                $current_line = explode(' ', $current_line);
                $current_line[count($current_line)-1] = str_replace("\n","",$current_line[count($current_line)-1]);
                $rides[] = new Ride(
                    new Location($current_line[0], $current_line[1]),
                    new Location($current_line[2], $current_line[3]),
                    $current_line[4],
                    $current_line[5]
                );
            }
        }
        return $rides;
    }
}

class Ride {
    const HEADING_TO_START = 1;
    const HEADING_TO_DESTINATION = 2;

    public $start;
    public $destination;
    public $earliestStart;
    public $latestFinish;
    public $taken; // boolean
    public $completed; // boolean
    public $timeTaken; // int
    public $timeArrivedToStart; // int
    public $timeCompleted; // int
    public $headingTo; // enum

    public function __construct(Location $start, Location $destination, $earliestStart, $latestFinish)
    {
        $this->start            = $start;
        $this->destination      = $destination;
        $this->earliestStart    = $earliestStart;
        $this->latestFinish     = $latestFinish;
        $this->headingTo        = self::HEADING_TO_START;
    }

    public function getDistance()
    {
        return abs($this->destination->x - $this->start->x) + abs($this->destination->y - $this->start->y);
    }

}

class Location {
    public $x;
    public $y;

    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function getDistanceFromOrigin()
    {
        return $this->x + $this->y;
    }

    public function findDistanceOfGivenLocation(Location $otherLocation)
    {
        return abs($this->x - $otherLocation->x) + abs($this->y - $otherLocation->y);
    }

    public function incrementX($delta_x = 1)
    {
        $this->x += $delta_x;
    }

    public function decrementX($delta_x = 1)
    {
        $this->x -= $delta_x;
    }

    public function incrementY($delta_y = 1)
    {
        $this->y += $delta_y;
    }

    public function decrementY($delta_y = 1)
    {
        $this->y -= $delta_y;
    }

    public function calculateNextMove(Ride $ride, $currentTime)
    {
        if ($ride->headingTo === Ride::HEADING_TO_DESTINATION) {
            if ($this->x < $ride->destination->x) {
                $this->incrementX(); return;
            } elseif ($this->x > $ride->destination->x) {
                $this->decrementX(); return;
            } elseif ($this->y < $ride->destination->y) {
                $this->incrementY(); return;
            } elseif ($this->y > $ride->destination->y) {
                $this->decrementY(); return;
            }
            // achieved destination of ride
        } else {
            if ($this->x < $ride->start->x) {
                $this->incrementX(); return;
            } elseif ($this->x > $ride->start->x) {
                $this->decrementX(); return;
            } elseif ($this->y < $ride->start->y) {
                $this->incrementY(); return;
            } elseif ($this->y > $ride->start->y) {
                $this->decrementY(); return;
            }
            // achieved start of ride
            // now redirect to destination of ride
            $ride->headingTo = Ride::HEADING_TO_DESTINATION;
            $ride->timeArrivedToStart = $currentTime;
        }
        return;
    }

}

class Vehicle {
    public $rides;
    public $currentLocation;
    public $isMoving; // boolean

    public function __construct($rides = [])
    {
        $this->rides = $rides;
        $this->currentLocation = new Location(0,0); // initial location of vehicle
    }

    public function assignRide(Ride $ride, $currentTime)
    {
        $this->rides[] = $ride;
        $this->isMoving = true;// started to move
        $ride->taken = true;
        $ride->timeTaken = $currentTime;
    }

    public function getNumRidesAssigned(){
        return count($this->rides);
    }

    public function setCurrentLocation(Location $newLocation)
    {
        $this->currentLocation = $newLocation;
    }

    public function findDistanceToRideStart(Ride $ride)
    {
        return $this->currentLocation->findDistanceOfGivenLocation($ride->start);
    }

    public function updateCurrentRideStatus($currentTime)
    {
        $currentRide = $this->rides[count($this->rides) - 1];
        $this->currentLocation->calculateNextMove($currentRide, $currentTime);
        if (
            !$currentRide->completed &&
            $this->currentLocation->findDistanceOfGivenLocation($currentRide->destination) === 0 &&
            $currentRide->headingTo === Ride::HEADING_TO_DESTINATION
        ) {
            // vehicle arrived to destination
            $this->isMoving = false;
            $currentRide->completed = true;
            $currentRide->timeCompleted = $currentTime;
        }
    }
}

class Fleet {
    public $vehicles;

    public function __construct($vehicles = [])
    {
        $this->vehicles = $vehicles;
    }

    public function addVehicle(Vehicle $vehicle)
    {
        $this->vehicles[] = $vehicle;
    }

    public function getNumberOfVehicles()
    {
        return count($this->vehicles);
    }

    public function getVehicles()
    {
        return $this->vehicles;
    }
}

class HashCode {
    // file names
    const LEVEL_1               = 'level_1.in'; // a_example.in
    const LEVEL_2               = 'level_2.in'; // b_should_be_easy.in
    const LEVEL_3               = 'level_3.in'; // c_no_hurry.in
    const LEVEL_4               = 'level_4.in'; // d_metropolis.in
    const LEVEL_5               = 'level_5.in'; // e_high_bonus.in

    public $R; // number of rows of the grid (1 ≤ R ≤ 10000)
    public $C; // number of columns of the grid (1 ≤ C ≤ 10000)
    public $F; // number of vehicles in the fleet (1 ≤ F ≤ 1000)
    public $N; // number of rides (1 ≤ N ≤ 10000)
    public $B; // per-ride bonus for starting the ride on time (1 ≤ B ≤ 10000)
    public $T; // number of steps in the simulation (1 ≤ T ≤ 10^9 )
    public $rides; // array of rides details, each ride is an array of 6 values: [[(0, 0), (1, 3), 2, 9], [], []...] => "ride from [0, 0] to [1, 3], earliest start 2, latest finish 9"
    public $fleet;

    function __construct($file_name) {
        $output = (new FileReader($file_name))->readFile();
        $this->rides = $output['rides'];
        $this->R = $output['R'];
        $this->C = $output['C'];
        $this->F = $output['F'];
        $this->N = $output['N'];
        $this->B = $output['B'];
        $this->T = $output['T'];
    }


    //////  LOGIC METHODS

    public function init()
    {
        $this->fleet = new Fleet();
        // loop of vehicles
        for ($i=0; $i<$this->F; $i++) {
            $this->fleet->addVehicle(new Vehicle());
        }
        $this->startSimulation();
    }

    public function startSimulation()
    {
        // start simulation
        for ($t=0; $t<$this->T; $t++) {
            foreach ($this->fleet->getVehicles() as $vehicle) {
                if (!$vehicle->isMoving) { // if is not moving then assign a ride to it

                    for ($i=0; $i<$this->N; $i++) {
                        // calculate if this vehicle can take this ride
                        if ($this->rideCanBeTakenByVehicle($this->rides[$i], $vehicle, $t)) {
                            // take this ride
                            $vehicle->assignRide($this->rides[$i], $t);
                            // start on another vehicle, this vehicle has a ride assigned now
                            break;
                        }
                    }
                } else { // if the vehicle is moving then check if its current ride is finished
                    $vehicle->updateCurrentRideStatus($t);
                }
            }
        }
    }

    private function rideCanBeTakenByVehicle(Ride $ride, Vehicle $vehicle, $currentTime)
    {
        $rideDistance = $ride->getDistance();
        $vehicleDistanceToRideStart = $vehicle->findDistanceToRideStart($ride);

        if (
            !$ride->taken &&
            !$ride->completed &&
            $currentTime + $vehicleDistanceToRideStart >= $ride->earliestStart &&
            $currentTime + $rideDistance + $vehicleDistanceToRideStart < $ride->latestFinish
        ) {
            return true;
        }

        return false;
    }


    //////  VIEW/SHOW METHODS

    public function showFirstLineParams() {
        echo $this->R.','.$this->C.','.$this->F.','.$this->N.','.$this->B.','.$this->T.PHP_EOL;
    }

    public function showRides() {
        echo json_encode($this->rides);
    }

    public function showVehicles() {
        echo json_encode($this->fleet->getVehicles());
    }
}

$hash_code = new HashCode(HashCode::LEVEL_1); // new instance passing file name to read

//$hash_code->showFirstLineParams();
//$hash_code->showRides();
$hash_code->init();
//$hash_code->showRides();
$hash_code->showVehicles();


?>