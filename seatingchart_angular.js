var app = angular.module("seatingApp",[]); 
app.controller("mainCtrl", function($scope,$http) {

	$scope.loadJSON = function() {
		$http({
  		method: 'GET',
  		url: 'data.json'
		}).then(function successCallback(response) {
    	$scope.event = response.data.event;
      $scope.seatHeight = $scope.setSeatHeight();
      $("#chart-wrapper").toggle();
  	}, function errorCallback(response) {
    // called asynchronously if an error occurs
    // or server returns response with an error status.
  	});
	};

  $scope.addSeat = function(col,index) {
  	console.log("addSeat");
  	var arr = $scope.event.cols[col];
  	var piece1 = arr.slice(0,index);
  	var piece2 = arr.slice(index);
  	var newseat = {fullname:"New", role:null, editview:false, marching:true};
  	piece1.push(newseat);
  	arr = piece1.concat(piece2);
  	$scope.event.cols[col] = arr;
    $scope.saveData();
  };

  $scope.editSeat = function(x) {
  	x.editview = true;
  };

  $scope.setRole = function(x,role) {
  	x.role = role;
  };

  $scope.saveInfo = function(x) {
  	x.editview = false;
  	$scope.saveData();
  };

  $scope.removeSeat = function(col,index) {
  	$scope.event.cols[col].splice(index, 1);
    $scope.saveData();
  };

  $scope.setMarching = function(x,bool) {
  	x.marching = bool;
  }

  $scope.saveData = function() {
    document.getElementById("seat-height").textContent = "@media print { div.collapsed { height:"+$scope.setSeatHeight()+"in; } }";
  	var obj = {
	    event: $scope.event
	  }
  	$.ajax({
	    type: 'POST',
	    url: 'save_data.php',
	    data: {json: JSON.stringify(obj)},
	    dataType: 'json'
		});
  }

  $scope.getEastColumns = function() {
    if($scope.hasOwnProperty("event")) {
      var obj = {
        "E1":$scope.event.cols.E1,
        "E2":$scope.event.cols.E2,
        "E3":$scope.event.cols.E3,
        "E4":$scope.event.cols.E4
      }
      return obj;
    }
  }
  $scope.getWestColumns = function() {
    if($scope.hasOwnProperty("event")) {
      var obj = {
        "W1":$scope.event.cols.W1,
        "W2":$scope.event.cols.W2,
        "W3":$scope.event.cols.W3,
        "W4":$scope.event.cols.W4
      }
      return obj;
    }
  }

  $scope.setSeatHeight = function() {
    var maxEast = Math.max($scope.event.cols.E1.length,$scope.event.cols.E2.length,$scope.event.cols.E3.length,$scope.event.cols.E4.length);
    var maxWest = Math.max($scope.event.cols.W1.length,$scope.event.cols.W2.length,$scope.event.cols.W3.length,$scope.event.cols.W4.length);
    var height = 7/(maxEast+maxWest);
    return height;
  }

});

app.config(['$httpProvider', function($httpProvider) {
    //initialize get if not there
    if (!$httpProvider.defaults.headers.get) {
        $httpProvider.defaults.headers.get = {};    
    }    
    //disable IE ajax request caching
    $httpProvider.defaults.headers.get['If-Modified-Since'] = 'Mon, 26 Jul 1997 05:00:00 GMT';
    // extra
    $httpProvider.defaults.headers.get['Cache-Control'] = 'no-cache';
    $httpProvider.defaults.headers.get['Pragma'] = 'no-cache';
}]);

app.filter('reverse', function() {
  return function(items) {
    return items.slice().reverse();
  };
});
