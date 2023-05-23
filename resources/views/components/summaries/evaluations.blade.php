@push('custom-scripts')
    @once
        <script type="text/javascript" src="{{ URL::asset ('js/echarts.min.js') }}"></script>
    @endonce
@endpush
<div class="overflow-x-auto w-full h-full">

    <div id="main" class="min-w-[15vw] min-h-[35vh]"></div>
    <script type="text/javascript">
        const allJsonData = {!! $summary !!};
        const studentsSeriesData = allJsonData["studentSeries"];
        const students = Object.keys(studentsSeriesData);

        {{-- 0=date, 1=successTime, 2=percentage, 3=successTime, 4=totalTime, 5=project name --}}
        let pi=0;
        const PI_DATE=pi++;
        const PI_CURRENT_PERCENTAGE=pi++;
        const PI_SUCCESS_TIME=pi++;
        const PI_TIME=pi++;
        const PI_TOTAL_SUCCESS_TIME=pi++;
        const PI_TOTAL_TIME=pi++;
        const PI_PROJECT_NAME=pi++;
        const PI_CLIENTS=pi++;

        {{-- 80% required to pass --}}
        let markLine = {
            //name: "min",
            yAxis: 80,
            symbol:'none', //startpoint
            emphasis:{
                disabled: true
            },
            label: {
                normal: {
                    show: false,
                }
            },
            lineStyle: {
                normal: {
                    color: "#53c048"
                }
            },
        };

        let series= [];
        {{-- seriesData indexes are student names --}}
        for(const student in studentsSeriesData){
            const studentSeriesData = studentsSeriesData[student];
            {{-- compute markers to identify succes/failure points --}}
            const markPointData=[];

            studentSeriesData.forEach(function(pointData){
                {{-- success --}}
                let color = '#30cc61';
                let rotate=0;
                if(pointData[2]==0){
                    color='#ec7671';
                    rotate=180;
                }

                {{-- Markpoints show red/green bulb on eval to show failure/success --}}
                markPointData.push({
                    name: 'name',
                    value: '',
                    xAxis: pointData[PI_DATE],
                    yAxis: pointData[PI_CURRENT_PERCENTAGE],
                    itemStyle: {color: color},
                    emphasis: {disabled: true},
                    symbol:'pin',
                    symbolSize:20,
                    symbolRotate:rotate,
                });
            });

            {{-- Add markline for each student in case of manual selection --}}
            series.push({
                name:student,
                data: studentSeriesData,
                type: 'line',
                ...(markLine!=null) && {markLine:{
                    symbol:'arrow',
                    data:[markLine]
                }},
                markPoint: {
                    data: markPointData
                },
                lineStyle:{
                    type:'dotted',
                    cap:'round'
                },
                /*endLabel: {
                    show: true,
                    formatter: function (params) {
                        return params.seriesName;
                    }
                }*/
            });

        }

        // Initialize the echarts instance based on the prepared dom
        var studentsEvolutionChart = echarts.init(document.getElementById('main'));

        // Specify the configuration items and data for the chart
        var studentsEvolutionChartOption = {
            title: [{text: '{{__('Evolution')}}'}],
            tooltip: {
                trigger: "axis",
                formatter: function (params) {
                    const pointData=params[0].data;{{-- with axis trigger adds an array dimension... --}}

                    return `{{__('Project')}}: <b>${pointData[PI_PROJECT_NAME]}</b> (${pointData[PI_CLIENTS]})<br />
{{__('Date')}}: ${pointData[PI_DATE]}<br />
{{__('Result')}}: ${pointData[PI_SUCCESS_TIME]}/${pointData[PI_TIME]}<br />
{{__('Summary')}}: ${pointData[PI_CURRENT_PERCENTAGE]}%`;
                },

            },
            ...(students.length>1) &&{legend: {
                data: students,
                orient:'vertical',
                left:'right'
            }},
            xAxis: {
                //min:'dataMin',
                //scale:true,
                type: 'time',
                //axisLabel:{formatter: '{dd}.{MMM}'},
                //interval:5
            },
            yAxis: {
                type: 'value',
                max:100,
            },
            ...(students.length==1) && {visualMap:
                { // the first visualMap component
                    type: 'piecewise', // defined to be continuous visualMap
                    //seriesIndex: 0,
                    dimension:1,
                    color:['#30cc61','#ec7671'],
                    min:0,
                    max:100,
                    show:false
                }},

            series: series
        };

        // Display the chart using the configuration items and data just specified.
        studentsEvolutionChart.setOption(studentsEvolutionChartOption);

        window.addEventListener('resize', function() {
            studentsEvolutionChart.resize();
        });
    </script>
</div>
