

//GAUGRECHART
$(function () {

  class GaugeChart {
    constructor(element, params) {
      this._element = element;
      this._initialValue = params.initialValue;
      this._higherValue = params.higherValue;
      this._title = params.title;
      this._subtitle = params.subtitle;
    }

    _buildConfig() {
      let element = this._element;

      return {
        value: this._initialValue,
        valueIndicator: {
          color: '#fff' },

        geometry: {
          startAngle: 180,
          endAngle: 360 },

        scale: {
          startValue: 0,
          endValue: this._higherValue,
          customTicks: [0,25, 50, 75,100,125,150,175,200],
          tick: {
            length: 8 },

          label: {
            font: {
              color: '#87959f',
              size: 9,
              family: '"Open Sans", sans-serif' } } },



        title: {
          verticalAlignment: 'bottom',
          text: this._title,
          font: {
            family: '"Open Sans", sans-serif',
            color: '#000',
            weight: 700,
            size: 22 },

          subtitle: {
            text: this._subtitle,
            font: {
              family: '"Open Sans", sans-serif',
              color: '#fff',
              weight: 700,
              size: 18 } } },



        onInitialized: function () {
          let currentGauge = $(element);
          let circle = currentGauge.find('.dxg-spindle-hole');
          let border = currentGauge.find('.dxg-spindle-border');

          currentGauge.find('.dxg-title text').first().attr('y', 48);
          currentGauge.find('.dxg-title text').last().attr('y', 28);
          currentGauge.find('.dxg-value-indicator').append(border, circle);
        } };


    }

    init() {
      $(this._element).dxCircularGauge(this._buildConfig());
    }}

    
  	$(document).ready(function () {
	  	// chart total dpk
	  	let params = {
			    initialValue: 0,
			    higherValue: 200,
			    subtitle: '0' 
			};
			params.title = 'ASET';
	  	let c_aset = new GaugeChart($('#c-aset'), params);
	    c_aset.init();

	    params.title = 'DPK';
	  	let c_dpk = new GaugeChart($('#c-dpk'), params);
	    c_dpk.init();

	    params.title = 'KREDIT';
	  	let c_kredit = new GaugeChart($('#c-kredit'), params);
	    c_kredit.init();

	    params.title = 'LABA';
	  	let c_laba = new GaugeChart($('#c-laba'), params);
	    c_laba.init();

	    get_arr_coa_other();
  	});
});

// CHART JS
// dpk
var c_2100000_penc,c_2120011_penc,c_2130000_penc,c_602_penc;
var c_2100000_line,c_2120011_line,c_2130000_line,c_602_line;
var c_2100000_scatter,c_2120011_scatter,c_2130000_scatter,c_602_scatter;
var c_2100000_half,c_2120011_half,c_2130000_half,c_602_half;

// kredit
var c_122502_penc,c_122506_penc,c_603_penc;
var c_122502_line,c_122506_line,c_603_line;
var c_122502_scatter,c_122506_scatter,c_603_scatter;
var c_122502_half,c_122506_half,c_603_half;

// laba
var c_59999_penc;
var c_59999_line;
var c_59999_scatter;

// pend & beban
var c_pend_half,c_beban_half;

var option_pie = {
  responsive: true,
  aspectRatio: 1,
  cutoutPercentage: 80,
  legend: {
    display: false
  },
  plugins: {
    title: {
      display: true,
      text: 'Chart.js Doughnut Chart'
    },
  },
}
var option_pie2 = {
  responsive: true,
  legend: {
    display: false
  }
}
var option_line = {
  responsive: true,
  elements: {
    point:{
      radius: 1
    }
  },
  tooltips: {
    "enabled": true,
    callbacks: {
      label: function(tooltipItem, data) {
        var label = data.datasets[tooltipItem.datasetIndex].label || '';

        if (label) {
            label += ': ';
        }
        label += customFormat(tooltipItem.yLabel / 1,0);
        return label;
      }
    }
	},
  stacked: false,
  plugins: {
    title: {
      display: true,
    },
  },
  legend: {
    position: 'bottom',
    labels: {
     	fontSize: 9,
    }
  },
  scales: {
  	xAxes: [{
  		gridLines: {
        display:false
      },
    	beginAtZero: true,
    	ticks: {
       	autoSkip: true,
       	display : true,
    	}
  	}],
    yAxes: [{
    	display:false,
    	gridLines: {
        display:false
      },
      ticks: {
       	display : false,
    	},
    }],
    },
    
};

var option_bar = {
  responsive: true,
  elements: {
    point:{
      radius: 1
    }
  },
  tooltips: {
    "enabled": true,
    callbacks: {
      label: function(tooltipItem, data) {
        var label = data.datasets[tooltipItem.datasetIndex].label || '';

        if (label) {
            label += ': ';
        }
        label += customFormat(tooltipItem.xLabel / 1,0);
        return label;
      }
    }
	},
  stacked: false,
  plugins: {
    title: {
      display: true,
    },
  },
  legend: {
  	display: false,
    position: 'bottom',
    labels: {
     	fontSize: 9,
    }
  },
  scales: {
  	xAxes: [{
  		display: false,
  	}],
    yAxes: [{
    	display:false,
    }],
    },
    
};

var option_bar2 = {
	tooltips: {
	    "enabled": true,
	    callbacks: {
	        label: function(tooltipItem, data) {
	            var label = data.datasets[tooltipItem.datasetIndex].label || '';

	            if (label) {
	                label += ': ';
	            }
	            label += numberFormat(tooltipItem.yLabel / 1,0);
	            return label;
	        }
	    }
	},
	maintainAspectRatio: false,
	responsive: true,
    scales: {
		xAxes: [{
			display: false,
	    beginAtZero: true,
	    ticks: {
	        autoSkip: false
	    },
	    gridLines: {
          display:false
      },
		}],
		yAxes: [{
      display: false,
      gridLines: {
            display:false
        },
      ticks: {
        	beginAtZero: true,
    	// Abbreviate the millions
    		callback: function(value, index, values) {
        	return numberFormat(value / 1,0);
    		}
  		}
    }],
  },

	legend: {
		display: true,
		position: 'bottom',
			labels: {
			boxWidth: 15,
		}
	}
};

var option_scatter = {
	responsive : true,
	tooltips: {
    callbacks: {
      label: function(tooltipItem, data) {
        var datasetLabel = data.datasets[tooltipItem.datasetIndex].label || 'Other';
   			return datasetLabel + ': (' + tooltipItem.xLabel + ', ' + tooltipItem.yLabel + ')';
      }
    }
  },
	plugins: {
    title: {
      display: true,
    },
  },
  legend: {
    display: false
  },
  scales: {
    xAxes: [{
      ticks: {
        // min: 0,
        // max: 160,
        fontSize: 7
      },
      gridLines: {
        display:false
      },
    }],
  	yAxes: [{
    	ticks: {
      	// min: -20,
      	// max: 20,
      	fontSize: 7,
    	},
    	gridLines: {
        display:false
      },
  	}],
	}
};

var option_pie_half = {
	responsive:true,
  circumference: 1 * Math.PI,
  rotation: 1 * Math.PI,
  legend: {
      display: false
  },
  tooltip: {
      enabled: false
  },
  cutoutPercentage: 80
}

$(function(){
	var ctx = document.getElementById('c_2100000_penc').getContext('2d');
	c_2100000_penc = new Chart(ctx, {
		type: 'doughnut',
		options : option_pie,
	});

	ctx = document.getElementById('c_2120011_penc').getContext('2d');
	c_2120011_penc = new Chart(ctx, {
		type: 'doughnut',
		options : option_pie,
	});

	ctx = document.getElementById('c_2130000_penc').getContext('2d');
	c_2130000_penc = new Chart(ctx, {
		type: 'doughnut',
		options : option_pie,
	});

	ctx = document.getElementById('c_602_penc').getContext('2d');
	c_602_penc = new Chart(ctx, {
		type: 'doughnut',
		options : option_pie,
	});

	// line
	ctx = document.getElementById('c_2100000_line').getContext('2d');
	c_2100000_line = new Chart(ctx, {
		type: 'line',
		options : option_line,
	});

	ctx = document.getElementById('c_2120011_line').getContext('2d');
	c_2120011_line = new Chart(ctx, {
		type: 'line',
		options : option_line,
	});

	ctx = document.getElementById('c_2130000_line').getContext('2d');
	c_2130000_line = new Chart(ctx, {
		type: 'line',
		options : option_line,
	});

	ctx = document.getElementById('c_602_line').getContext('2d');
	c_602_line = new Chart(ctx, {
		type: 'line',
		options : option_line,
	});

	ctx = document.getElementById('c_2100000_scatter').getContext('2d');
	c_2100000_scatter = new Chart(ctx, {
		type: 'scatter',
		options : option_scatter,
	});

	ctx = document.getElementById('c_2120011_scatter').getContext('2d');
	c_2120011_scatter = new Chart(ctx, {
		type: 'scatter',
		options : option_scatter,
	});

	ctx = document.getElementById('c_2130000_scatter').getContext('2d');
	c_2130000_scatter = new Chart(ctx, {
		type: 'scatter',
		options : option_scatter,
	});

	ctx = document.getElementById('c_602_scatter').getContext('2d');
	c_602_scatter = new Chart(ctx, {
		type: 'scatter',
		options : option_scatter,
	});

	ctx = document.getElementById("c_2100000_half");
  c_2100000_half = new Chart(ctx, {
    type: 'doughnut',
    options: option_pie
  });

  ctx = document.getElementById("c_2120011_half");
  c_2120011_half = new Chart(ctx, {
    type: 'doughnut',
    options: option_pie
  });

  ctx = document.getElementById("c_2130000_half");
  c_2130000_half = new Chart(ctx, {
    type: 'doughnut',
    options: option_pie
  });

  ctx = document.getElementById("c_602_half");
  c_602_half = new Chart(ctx, {
    type: 'pie',
    options: option_pie2
  });

  // kr
  ctx = document.getElementById('c_122502_penc').getContext('2d');
	c_122502_penc = new Chart(ctx, {
		type: 'doughnut',
		options : option_pie,
	});

	ctx = document.getElementById('c_122506_penc').getContext('2d');
	c_122506_penc = new Chart(ctx, {
		type: 'doughnut',
		options : option_pie,
	});

	ctx = document.getElementById('c_603_penc').getContext('2d');
	c_603_penc = new Chart(ctx, {
		type: 'doughnut',
		options : option_pie,
	});

	ctx = document.getElementById('c_122502_line').getContext('2d');
	c_122502_line = new Chart(ctx, {
		type: 'line',
		options : option_line,
	});

	ctx = document.getElementById('c_122506_line').getContext('2d');
	c_122506_line = new Chart(ctx, {
		type: 'line',
		options : option_line,
	});

	ctx = document.getElementById('c_603_line').getContext('2d');
	c_603_line = new Chart(ctx, {
		type: 'line',
		options : option_line,
	});

	ctx = document.getElementById('c_122502_scatter').getContext('2d');
	c_122502_scatter = new Chart(ctx, {
		type: 'scatter',
		options : option_scatter,
	});

	ctx = document.getElementById('c_122506_scatter').getContext('2d');
	c_122506_scatter = new Chart(ctx, {
		type: 'scatter',
		options : option_scatter,
	});

	ctx = document.getElementById('c_603_scatter').getContext('2d');
	c_603_scatter = new Chart(ctx, {
		type: 'scatter',
		options : option_scatter,
	});

	ctx = document.getElementById('c_122502_half').getContext('2d');
	c_122502_half = new Chart(ctx, {
		type: 'horizontalBar',
		options : option_bar,
	});

	ctx = document.getElementById('c_122506_half').getContext('2d');
	c_122506_half = new Chart(ctx, {
		type: 'horizontalBar',
		options : option_bar,
	});

	ctx = document.getElementById('c_603_half').getContext('2d');
	c_603_half = new Chart(ctx, {
		type: 'horizontalBar',
		options : option_bar,
	});
	
	// lb
	ctx = document.getElementById('c_59999_penc').getContext('2d');
	c_59999_penc = new Chart(ctx, {
		type: 'doughnut',
		options : option_pie,
	});
	ctx = document.getElementById('c_59999_line').getContext('2d');
	c_59999_line = new Chart(ctx, {
		type: 'line',
		options : option_line,
	});
	ctx = document.getElementById('c_59999_scatter').getContext('2d');
	c_59999_scatter = new Chart(ctx, {
		type: 'scatter',
		options : option_scatter,
	});

	// pend & beban
	ctx = document.getElementById("c_pend_half");
  c_pend_half = new Chart(ctx, {
    type: 'pie',
    options: option_pie2
  });
  ctx = document.getElementById("c_beban_half");
  c_beban_half = new Chart(ctx, {
    type: 'pie',
    options: option_pie2
  });

})

$('#filter_tahun').change(function(){loadData();});
$('#filter_cabang').change(function(){loadData();});
$('#filter_bulan').change(function(){loadData();});
var controller = 'mac2';

var content_status = false;
function get_arr_coa_other(){
	var page = base_url +'transaction/'+controller+'/get_arr_coa_other';
	$.ajax({
			url 	: page,
			type	: 'post',
			dataType: 'json',
			success	: function(response) {
				$.each(response,function(k,v){
					var val = 'chart_bar_'+v;
					var ctx = document.getElementById(val).getContext('2d');
					window[val] = new Chart(ctx, {
						type: 'bar',
						options : option_bar2,
					});

					$("#"+val).click(function(evt){
					   	var coa = v;
					   	var activePoints = window[val].getElementsAtEvent(evt);
					   	if(activePoints.length>0){
					   		window.open(base_url+'transaction/rekap_mac_group?coa='+coa, '_blank');
					   	}
				    });
				});
				content_status = true;
				loadData();
			}
		});
}

var xhr_ajax = null;
function loadData(){
	if(!content_status){
		return false;
	}

	var cabang = $('#filter_cabang option:selected').val();
	if(!cabang){return false;}
	cLoader.open(lang.memuat_data + '...');
    if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }

    var page = base_url + 'transaction/'+controller+'/data';
    page += '/'+ $('#filter_tahun').val();
    page += '/'+ $('#filter_cabang').val();
    page += '/'+ $('#filter_bulan').val();

  	xhr_ajax = $.ajax({
      url: page,
      type: 'post',
			data : {},
      dataType: 'json',
      success: function(res){
      	xhr_ajax = null;
      	
      	if(!res.status){
      		cLoader.close();
      		cAlert.open(res.message,'failed');
      		return false;
      	}

      	// # dpk
      	let c_dpk = $('#c-dpk').dxCircularGauge('instance');
        let gaugeElement = $(c_dpk._$element[0]);
        let val = res.dpk.dpk_penc;
        gaugeElement.find('.dxg-title text').last().html(`${val}%`);
        c_dpk.value(val);

      	$.each(res.dpk.class,function(k,v){
      		$(k).html(v);
      	})
      	$.each(res.dpk.chart,function(k,v){
      		window[k].data = v;
      		window[k].update();
      	})
      	cLoader.close();
      	loadKredit();
      }
    });
}

var xhr_ajax_kre = null;
function loadKredit(){
	var cabang = $('#filter_cabang option:selected').val();
	if(!cabang){return false;}
	cLoader.open(lang.memuat_data + '...');
    if( xhr_ajax_kre != null ) {
        xhr_ajax_kre.abort();
        xhr_ajax_kre = null;
    }

  var page = base_url + 'transaction/'+controller+'/data_kredit';
  page += '/'+ $('#filter_tahun').val();
  page += '/'+ $('#filter_cabang').val();
  page += '/'+ $('#filter_bulan').val();

	xhr_ajax_kre = $.ajax({
    url: page,
    type: 'post',
		data : {},
    dataType: 'json',
    success: function(res){
    	xhr_ajax_kre = null;
    	
    	if(!res.status){
    		cLoader.close();
    		cAlert.open(res.message,'failed');
    		return false;
    	}

    	// # kredit
    	let c_kredit = $('#c-kredit').dxCircularGauge('instance');
      let gaugeElement = $(c_kredit._$element[0]);
      let val = res.kredit.kredit_penc;
      gaugeElement.find('.dxg-title text').last().html(`${val}%`);
      c_kredit.value(val);

    	$.each(res.kredit.class,function(k,v){
    		$(k).html(v);
    	})
    	$.each(res.kredit.chart,function(k,v){
    		window[k].data = v;
    		window[k].update();
    	})
    	cLoader.close();
    	loadEcl();
    }
  });
}

var xhr_ajax_ecl = null;
function loadEcl(){
	var cabang = $('#filter_cabang option:selected').val();
	if(!cabang){return false;}
	cLoader.open(lang.memuat_data + '...');
    if( xhr_ajax_ecl != null ) {
        xhr_ajax_ecl.abort();
        xhr_ajax_ecl = null;
    }

  var page = base_url + 'transaction/'+controller+'/data_ecl';
  page += '/'+ $('#filter_tahun').val();
  page += '/'+ $('#filter_cabang').val();
  page += '/'+ $('#filter_bulan').val();

	xhr_ajax_ecl = $.ajax({
    url: page,
    type: 'post',
		data : {},
    dataType: 'json',
    success: function(res){
    	xhr_ajax_ecl = null;
    	
    	if(!res.status){
    		cLoader.close();
    		cAlert.open(res.message,'failed');
    		return false;
    	}
    	$.each(res.ecl.class,function(k,v){
    		$(k).html(v);
    	})
    	cLoader.close();
    	loadLaba();
    }
  });
}

var xhr_ajax_laba = null;
function loadLaba(){
	var cabang = $('#filter_cabang option:selected').val();
	if(!cabang){return false;}
	cLoader.open(lang.memuat_data + '...');
    if( xhr_ajax_laba != null ) {
        xhr_ajax_laba.abort();
        xhr_ajax_laba = null;
    }

  var page = base_url + 'transaction/'+controller+'/data_laba';
  page += '/'+ $('#filter_tahun').val();
  page += '/'+ $('#filter_cabang').val();
  page += '/'+ $('#filter_bulan').val();

	xhr_ajax_laba = $.ajax({
    url: page,
    type: 'post',
		data : {},
    dataType: 'json',
    success: function(res){
    	xhr_ajax_laba = null;
    	
    	if(!res.status){
    		cLoader.close();
    		cAlert.open(res.message,'failed');
    		return false;
    	}

    	let c_laba = $('#c-laba').dxCircularGauge('instance');
      let gaugeElement = $(c_laba._$element[0]);
      let val = res.laba.laba_penc;
      gaugeElement.find('.dxg-title text').last().html(`${val}%`);
      c_laba.value(val);

    	$.each(res.laba.class,function(k,v){
    		$(k).html(v);
    	})

    	$.each(res.laba.chart,function(k,v){
    		window[k].data = v;
    		window[k].update();
    	})
    	cLoader.close();
    	loadPendBeban();
    }
  });
}

var xhr_pend_beban = null;
function loadPendBeban(){
	var cabang = $('#filter_cabang option:selected').val();
	if(!cabang){return false;}
	cLoader.open(lang.memuat_data + '...');
    if( xhr_pend_beban != null ) {
        xhr_pend_beban.abort();
        xhr_pend_beban = null;
    }

  var page = base_url + 'transaction/'+controller+'/data_pend_beban';
  page += '/'+ $('#filter_tahun').val();
  page += '/'+ $('#filter_cabang').val();
  page += '/'+ $('#filter_bulan').val();

	xhr_pend_beban = $.ajax({
    url: page,
    type: 'post',
		data : {},
    dataType: 'json',
    success: function(res){
    	xhr_pend_beban = null;
    	
    	if(!res.status){
    		cLoader.close();
    		cAlert.open(res.message,'failed');
    		return false;
    	}

    	$.each(res.data.class,function(k,v){
    		$(k).html(v);
    	})
    	$.each(res.data.chart,function(k,v){
    		window[k].data = v;
    		window[k].update();
    	})
    	cLoader.close();
    	loadOther();
    }
  });
}

var xhr_other = null;
function loadOther(){
	var cabang = $('#filter_cabang option:selected').val();
	if(!cabang){return false;}
	cLoader.open(lang.memuat_data + '...');
    if( xhr_other != null ) {
        xhr_other.abort();
        xhr_other = null;
    }

  var page = base_url + 'transaction/'+controller+'/data_other';
  page += '/'+ $('#filter_tahun').val();
  page += '/'+ $('#filter_cabang').val();
  page += '/'+ $('#filter_bulan').val();

	xhr_other = $.ajax({
    url: page,
    type: 'post',
		data : {},
    dataType: 'json',
    success: function(res){
    	xhr_other = null;
    	
    	if(!res.status){
    		cLoader.close();
    		cAlert.open(res.message,'failed');
    		return false;
    	}

    	$.each(res.data.class,function(k,v){
    		$(k).html(v);
    	})
    	$.each(res.data.chart,function(k,v){
    		window[k].data = v;
    		window[k].update();
    	})
    	loadAsset();
    	cLoader.close();
    }
  });
}

var xhr_ajax_asset = null;
function loadAsset(){
	var cabang = $('#filter_cabang option:selected').val();
	if(!cabang){return false;}
	cLoader.open(lang.memuat_data + '...');
    if( xhr_ajax_asset != null ) {
        xhr_ajax_asset.abort();
        xhr_ajax_asset = null;
    }

  var page = base_url + 'transaction/'+controller+'/data_asset';
  page += '/'+ $('#filter_tahun').val();
  page += '/'+ $('#filter_cabang').val();
  page += '/'+ $('#filter_bulan').val();

	xhr_ajax_asset = $.ajax({
    url: page,
    type: 'post',
		data : {},
    dataType: 'json',
    success: function(res){
    	xhr_ajax_asset = null;
    	
    	if(!res.status){
    		cLoader.close();
    		cAlert.open(res.message,'failed');
    		return false;
    	}

    	let c_aset = $('#c-aset').dxCircularGauge('instance');
      let gaugeElement = $(c_aset._$element[0]);
      let val = res.data.aset_penc;
      gaugeElement.find('.dxg-title text').last().html(`${val}%`);
      c_aset.value(val);

    	$.each(res.data.class,function(k,v){
    		$(k).html(v);
    	})
    	cLoader.close();
    	loadPendBebanO();
    }
  });
}

var xhr_pend_beban_o = null;
function loadPendBebanO(){
	var cabang = $('#filter_cabang option:selected').val();
	if(!cabang){return false;}
	cLoader.open(lang.memuat_data + '...');
    if( xhr_pend_beban_o != null ) {
        xhr_pend_beban_o.abort();
        xhr_pend_beban_o = null;
    }

  var page = base_url + 'transaction/'+controller+'/data_pend_beban_o';
  page += '/'+ $('#filter_tahun').val();
  page += '/'+ $('#filter_cabang').val();
  page += '/'+ $('#filter_bulan').val();

	xhr_pend_beban_o = $.ajax({
    url: page,
    type: 'post',
		data : {},
    dataType: 'json',
    success: function(res){
    	xhr_pend_beban_o = null;
    	
    	if(!res.status){
    		cLoader.close();
    		cAlert.open(res.message,'failed');
    		return false;
    	}

    	$.each(res.data.class,function(k,v){
    		$(k).html(v);
    	})
    	cLoader.close();
    }
  });
}
