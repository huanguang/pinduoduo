<div style="width:90%;">
    <canvas id="canvas"></canvas>
</div>
<br>
<br>
<button style="display: none;" id="randomizeData">Randomize Data</button>
<button style="display: none;" id="addDataset">Add Dataset</button>
<button style="display: none;" id="removeDataset">Remove Dataset</button>
<button id="addData" class="btn btn-sm btn-dropbox">往前一天</button>
<button id="removeData" class="btn btn-sm btn-dropbox">往后一天</button>
<script src="/vendor/Chart.js/config.js"></script>
<script>
    var strData = {!! $arr !!};
    var strData2 = {!! $arr2 !!};
    var MONTHS = [{!! $str !!}];
    var config = {
        type: 'line',
        data: {
            labels: [{!! $str2 !!}],
            datasets: [{
                label: '待付款',
                backgroundColor: window.chartColors.orange,
                borderColor: window.chartColors.orange,
                data: strData2[0],
                fill: false,
            }, {
                label: '待发货',
                fill: false,
                backgroundColor: window.chartColors.grey,
                borderColor: window.chartColors.grey,
                data: strData2[1],
            }, {
                label: '待收货',
                fill: false,
                backgroundColor: window.chartColors.purple,
                borderColor: window.chartColors.purple,
                data: strData2[2],
            }, {
                label: '待评价',
                fill: false,
                backgroundColor: window.chartColors.blue,
                borderColor: window.chartColors.blue,
                data: strData2[3],
            }, {
                label: '交易取消',
                fill: false,
                backgroundColor: window.chartColors.green,
                borderColor: window.chartColors.green,
                data: strData2[4],
            }, {
                label: '等待到账',
                fill: false,
                backgroundColor: window.chartColors.yellow,
                borderColor: window.chartColors.yellow,
                data: strData2[5],
            }, {
                label: '拼单成功，待发货',
                fill: false,
                backgroundColor: window.chartColors.red,
                borderColor: window.chartColors.red,
                data: strData2[6],
            }]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: '近三十天订单数据'
            },
            tooltips: {
                mode: 'index',
                intersect: false,
            },
            hover: {
                mode: 'nearest',
                intersect: true
            },
            scales: {
                xAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: '日期'
                    }
                }],
                yAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: '单位（元）'
                    }
                }]
            }
        }
    };

    window.onload = function() {
        var ctx = document.getElementById('canvas').getContext('2d');
        window.myLine = new Chart(ctx, config);
    };

    document.getElementById('randomizeData').addEventListener('click', function() {
        config.data.datasets.forEach(function(dataset) {
            dataset.data = dataset.data.map(function() {
                return randomScalingFactor();
            });

        });

        window.myLine.update();
    });

    var colorNames = Object.keys(window.chartColors);
    document.getElementById('addDataset').addEventListener('click', function() {
        var colorName = colorNames[config.data.datasets.length % colorNames.length];
        var newColor = window.chartColors[colorName];
        var newDataset = {
            label: 'Dataset ' + config.data.datasets.length,
            backgroundColor: newColor,
            borderColor: newColor,
            data: [],
            fill: false
        };

        for (var index = 0; index < config.data.labels.length; ++index) {
            newDataset.data.push(randomScalingFactor());
        }

        config.data.datasets.push(newDataset);
        window.myLine.update();
    });

    document.getElementById('addData').addEventListener('click', function() {
        if (config.data.datasets.length > 0) {
            var month = MONTHS[config.data.labels.length % MONTHS.length];
            config.data.labels.push(month);
            //strData[month]
            //config.data.datasets.push();
            config.data.datasets[0].data.push(strData[month].qxCount);
            config.data.datasets[1].data.push(strData[month].dzfCount);
            config.data.datasets[2].data.push(strData[month].dfhCount);
            config.data.datasets[3].data.push(strData[month].dshCount);
            config.data.datasets[4].data.push(strData[month].ywcCount);
            config.data.datasets[5].data.push(strData[month].thzCount);
            config.data.datasets[5].data.push(strData[month].ytkCount);
            //config.data.datasets.forEach(function(dataset) {
                //dataset.data.push(10);
                //console.log(dataset)
            //});

            window.myLine.update();
        }
    });

    document.getElementById('removeDataset').addEventListener('click', function() {
        config.data.datasets.splice(0, 1);
        window.myLine.update();
    });

    document.getElementById('removeData').addEventListener('click', function() {
        config.data.labels.splice(-1, 1); // remove the label first

        config.data.datasets.forEach(function(dataset) {
            dataset.data.pop();
        });

        window.myLine.update();
    });
</script>