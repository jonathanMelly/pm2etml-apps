@push('custom-scripts')
    @once
        <script type="text/javascript" src="{{ URL::asset ('js/echarts.min.js') }}"></script>
    @endonce
@endpush

<div id="summariesCharts" class="w-[100%] overflow-x-auto">
</div>
<div id="evolutionCharts" class="w-[100%]">
</div>
<script type="text/javascript">
    String.prototype.limit = function(max) {
        const after="...";
        return this.length>max-after.length ? this.substring(0, max-after.length) + after  : this.toString();
    }

    const theme = '{{\App\Http\Middleware\Theme::isDark(session('theme'))?'dark':'light'}}';

    const red='#ec7671';
    const green='#30cc61';
    const yellow='#dce716';

    const allJsonData = {!! $summary !!};
    {{--const allJsonData = {"evaluations":{"cin1c":{"ryan k.":[["2023-02-22 01:39",100,24,24,24,24,"P_WebDB : Mod\u00e9liser un site web","antoine m."]],"quentin m.":[["2023-02-22 01:39",100,24,24,24,24,"P_WebDB : Mod\u00e9liser un site web","antoine m."]],"matthieu r.":[["2023-02-22 01:39",100,24,24,24,24,"P_WebDB : Mod\u00e9liser un site web","antoine m."]],"julian luca d.":[["2023-02-22 01:39",100,24,24,24,24,"P_WebDB : Mod\u00e9liser un site web","antoine m."],["2023-04-26 02:15",100,24,24,48,48,"P_SYS : Premier PC","helder c."]],"anakin jeremy j.":[["2023-02-22 01:39",100,24,24,24,24,"P_WebDB : Mod\u00e9liser un site web","antoine m."]],"julien m.":[["2023-02-22 01:39",100,24,24,24,24,"P_WebDB : Mod\u00e9liser un site web","antoine m."]],"esteban alexis l.":[["2023-02-22 01:39",100,24,24,24,24,"P_WebDB : Mod\u00e9liser un site web","antoine m."],["2023-04-26 02:15",100,24,24,48,48,"P_SYS : Premier PC","helder c."]],"thibaud no\u00e9 r.":[["2023-02-22 01:39",100,24,24,24,24,"P_WebDB : Mod\u00e9liser un site web","antoine m."],["2023-04-26 02:15",100,24,24,48,48,"P_SYS : Premier PC","helder c."]],"thomas m.":[["2023-02-22 01:39",100,24,24,24,24,"P_WebDB : Mod\u00e9liser un site web","antoine m."],["2023-04-26 02:15",100,24,24,48,48,"P_SYS : Premier PC","helder c."]],"mathias andr\u00e9 m.":[["2023-02-22 01:39",100,24,24,24,24,"P_WebDB : Mod\u00e9liser un site web","antoine m."],["2023-04-26 02:15",50,0,24,24,48,"P_SYS : Premier PC","helder c."]],"yohan jacques c.":[["2023-04-26 02:15",100,24,24,24,24,"P_SYS : Premier PC","helder c."]],"cyril constant n.":[["2023-02-22 01:39",100,24,24,24,24,"P_WebDB : Mod\u00e9liser un site web","antoine m."],["2023-04-26 02:15",100,24,24,48,48,"P_SYS : Premier PC","helder c."]],"francesco f.":[["2023-02-22 01:39",100,24,24,24,24,"P_WebDB : Mod\u00e9liser un site web","antoine m."],["2023-04-26 02:15",50,0,24,24,48,"P_SYS : Premier PC","helder c."]],"rayan timo b.":[["2023-02-22 01:39",100,24,24,24,24,"P_WebDB : Mod\u00e9liser un site web","antoine m."]],"timothy noah j.":[["2023-02-22 01:39",100,24,24,24,24,"P_WebDB : Mod\u00e9liser un site web","antoine m."],["2023-04-26 02:15",100,24,24,48,48,"P_SYS : Premier PC","helder c."]],"mussa a.":[["2023-02-22 01:39",100,24,24,24,24,"P_WebDB : Mod\u00e9liser un site web","antoine m."],["2023-04-26 02:15",100,24,24,48,48,"P_SYS : Premier PC","helder c."]]},"cin1b":{"alo\u00efs michel charles m.":[["2022-11-04 07:51",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","bertrand s."],["2022-11-04 09:33",100,24,24,56,56,"P_SYS : Premier PC","bertrand s."],["2023-04-26 02:23",100,24,24,80,80,"P_WebDB : Mod\u00e9liser un site web","helder c."]],"mattis v.":[["2022-11-04 07:51",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","bertrand s."],["2022-11-04 09:37",57,0,24,32,56,"P_SYS : Premier PC","bertrand s."],["2023-04-26 02:23",40,0,24,32,80,"P_WebDB : Mod\u00e9liser un site web","helder c."]],"mathis b.":[["2022-11-03 04:02",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","bertrand s."],["2022-11-04 09:37",57,0,24,32,56,"P_SYS : Premier PC","bertrand s."],["2023-04-26 02:23",40,0,24,32,80,"P_WebDB : Mod\u00e9liser un site web","helder c."]],"karim d.":[["2022-11-04 07:50",0,0,32,0,32,"P_INNO : Voyage d'\u00e9tude","bertrand s."],["2022-11-04 09:35",0,0,24,0,56,"P_SYS : Premier PC","bertrand s."],["2023-04-26 02:23",0,0,24,0,80,"P_WebDB : Mod\u00e9liser un site web","helder c."]],"danilo z.":[["2022-11-04 07:51",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","bertrand s."],["2022-11-04 09:37",57,0,24,32,56,"P_SYS : Premier PC","bertrand s."],["2023-04-26 02:23",40,0,24,32,80,"P_WebDB : Mod\u00e9liser un site web","helder c."]],"ethan aymeric s.":[["2022-11-04 07:51",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","bertrand s."],["2022-11-04 09:33",100,24,24,56,56,"P_SYS : Premier PC","bertrand s."],["2023-02-23 08:19",100,24,24,80,80,"P_WebDB : Analyse d'informations personnelles","antoine m."],["2023-04-22 05:41",100,24,24,104,104,"Infrastructure r\u00e9seau d'une salle de cours","karim b."],["2023-04-26 02:23",100,24,24,128,128,"P_WebDB : Mod\u00e9liser un site web","helder c."]],"sofiene habib b.":[["2022-11-03 04:02",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","bertrand s."],["2022-11-04 09:35",57,0,24,32,56,"P_SYS : Premier PC","bertrand s."],["2023-04-26 02:23",70,24,24,56,80,"P_WebDB : Mod\u00e9liser un site web","helder c."]],"adrian federico t.":[["2022-11-04 07:51",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","bertrand s."],["2022-11-04 09:33",100,24,24,56,56,"P_SYS : Premier PC","bertrand s."],["2023-04-26 02:23",100,24,24,80,80,"P_WebDB : Mod\u00e9liser un site web","helder c."]],"mohammad dawood a.":[["2022-11-03 04:01",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","bertrand s."],["2022-11-04 09:33",100,24,24,56,56,"P_SYS : Premier PC","bertrand s."],["2023-02-23 08:18",100,24,24,80,80,"P_WebDB : Analyse d'informations personnelles","antoine m."],["2023-04-26 02:23",77,0,24,80,104,"P_WebDB : Mod\u00e9liser un site web","helder c."]],"mateen salem k.":[["2022-11-04 07:51",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","bertrand s."],["2022-11-04 09:37",57,0,24,32,56,"P_SYS : Premier PC","bertrand s."],["2023-04-26 02:23",70,24,24,56,80,"P_WebDB : Mod\u00e9liser un site web","helder c."]],"abiram m.":[["2022-11-04 07:51",0,0,32,0,32,"P_INNO : Voyage d'\u00e9tude","bertrand s."],["2022-11-04 09:37",0,0,24,0,56,"P_SYS : Premier PC","bertrand s."],["2023-04-26 02:23",0,0,24,0,80,"P_WebDB : Mod\u00e9liser un site web","helder c."]],"younes abdelhamid c.":[["2022-11-03 04:02",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","bertrand s."],["2022-11-04 09:35",57,0,24,32,56,"P_SYS : Premier PC","bertrand s."]],"evan dimitri f.":[["2022-11-04 07:51",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","bertrand s."],["2022-11-04 09:37",57,0,24,32,56,"P_SYS : Premier PC","bertrand s."],["2023-04-26 02:23",70,24,24,56,80,"P_WebDB : Mod\u00e9liser un site web","helder c."]],"julien pierre m.":[["2022-11-04 07:51",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","bertrand s."],["2022-11-04 09:33",100,24,24,56,56,"P_SYS : Premier PC","bertrand s."],["2023-04-26 02:23",100,24,24,80,80,"P_WebDB : Mod\u00e9liser un site web","helder c."]],"evin p.":[["2022-11-04 07:51",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","bertrand s."],["2022-11-04 09:37",57,0,24,32,56,"P_SYS : Premier PC","bertrand s."],["2023-04-26 02:23",70,24,24,56,80,"P_WebDB : Mod\u00e9liser un site web","helder c."]]},"cin1a":{"lucas pierre nangawi k.":[["2022-12-09 03:46",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","alain g."]],"diego fernando m.":[["2022-12-09 03:46",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","alain g."]],"giovanni b.":[["2022-12-09 03:46",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","alain g."],["2023-01-27 02:30",100,24,24,56,56,"P_WebDB : Analyse d'informations personnelles","alain g."]],"luke raimondo m.":[["2022-12-09 03:46",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","alain g."]],"alan b.":[["2022-12-09 03:46",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","alain g."]],"teo l.":[["2022-12-09 03:46",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","alain g."]],"siem b.":[["2022-12-09 03:46",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","alain g."]],"nikola g.":[["2022-12-09 03:46",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","alain g."]],"mohamed zaahid m.":[["2022-12-09 03:46",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","alain g."]],"even gavri\u00ebl m.":[["2022-12-09 03:46",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","alain g."]],"rui pedro r.":[["2022-12-09 03:46",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","alain g."]],"william andres t.":[["2022-12-09 03:46",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","alain g."]],"amir z.":[["2022-12-09 03:46",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","alain g."]],"nima amir aram z.":[["2022-12-09 03:46",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","alain g."]],"dario jhesuanj c.":[["2022-12-09 03:46",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","alain g."]],"surenthar j.":[["2022-12-09 03:46",100,32,32,32,32,"P_INNO : Voyage d'\u00e9tude","alain g."]]},"cin2b":{"nelson p.":[["2023-04-26 02:23",100,24,24,24,24,"P_WebDB : Mod\u00e9liser un site web","helder c."]],"matej r.":[["2023-04-26 02:29",0,0,24,0,24,"P_WebDB : Mod\u00e9liser un site web","helder c."]]}},"summaries":{"cin1c":{"all":[14,["ryan k.","quentin m.","matthieu r.","julian luca d.","anakin jeremy j.","julien m.","esteban alexis l.","thibaud no\u00e9 r.","thomas m.","yohan jacques c.","cyril constant n.","rayan timo b.","timothy noah j.","mussa a."],2,["mathias andr\u00e9 m.","francesco f."]],"ryan k.":[24,["P_WebDB : Mod\u00e9liser un site web"],0,[]],"quentin m.":[24,["P_WebDB : Mod\u00e9liser un site web"],0,[]],"matthieu r.":[24,["P_WebDB : Mod\u00e9liser un site web"],0,[]],"julian luca d.":[48,["P_WebDB : Mod\u00e9liser un site web","P_SYS : Premier PC"],0,[]],"anakin jeremy j.":[24,["P_WebDB : Mod\u00e9liser un site web"],0,[]],"julien m.":[24,["P_WebDB : Mod\u00e9liser un site web"],0,[]],"esteban alexis l.":[48,["P_WebDB : Mod\u00e9liser un site web","P_SYS : Premier PC"],0,[]],"thibaud no\u00e9 r.":[48,["P_WebDB : Mod\u00e9liser un site web","P_SYS : Premier PC"],0,[]],"thomas m.":[48,["P_WebDB : Mod\u00e9liser un site web","P_SYS : Premier PC"],0,[]],"mathias andr\u00e9 m.":[24,["P_WebDB : Mod\u00e9liser un site web"],24,["P_SYS : Premier PC"]],"yohan jacques c.":[24,["P_SYS : Premier PC"],0,[]],"cyril constant n.":[48,["P_WebDB : Mod\u00e9liser un site web","P_SYS : Premier PC"],0,[]],"francesco f.":[24,["P_WebDB : Mod\u00e9liser un site web"],24,["P_SYS : Premier PC"]],"rayan timo b.":[24,["P_WebDB : Mod\u00e9liser un site web"],0,[]],"timothy noah j.":[48,["P_WebDB : Mod\u00e9liser un site web","P_SYS : Premier PC"],0,[]],"mussa a.":[48,["P_WebDB : Mod\u00e9liser un site web","P_SYS : Premier PC"],0,[]]},"cin1b":{"all":[4,["alo\u00efs michel charles m.","ethan aymeric s.","adrian federico t.","julien pierre m."],11,["mattis v.","mathis b.","karim d.","danilo z.","sofiene habib b.","mohammad dawood a.","mateen salem k.","abiram m.","younes abdelhamid c.","evan dimitri f.","evin p."]],"alo\u00efs michel charles m.":[80,["P_INNO : Voyage d'\u00e9tude","P_SYS : Premier PC","P_WebDB : Mod\u00e9liser un site web"],0,[]],"mattis v.":[32,["P_INNO : Voyage d'\u00e9tude"],48,["P_SYS : Premier PC","P_WebDB : Mod\u00e9liser un site web"]],"mathis b.":[32,["P_INNO : Voyage d'\u00e9tude"],48,["P_SYS : Premier PC","P_WebDB : Mod\u00e9liser un site web"]],"karim d.":[0,[],80,["P_INNO : Voyage d'\u00e9tude","P_SYS : Premier PC","P_WebDB : Mod\u00e9liser un site web"]],"danilo z.":[32,["P_INNO : Voyage d'\u00e9tude"],48,["P_SYS : Premier PC","P_WebDB : Mod\u00e9liser un site web"]],"ethan aymeric s.":[128,["P_INNO : Voyage d'\u00e9tude","P_SYS : Premier PC","P_WebDB : Analyse d'informations personnelles","Infrastructure r\u00e9seau d'une salle de cours","P_WebDB : Mod\u00e9liser un site web"],0,[]],"sofiene habib b.":[56,["P_INNO : Voyage d'\u00e9tude","P_WebDB : Mod\u00e9liser un site web"],24,["P_SYS : Premier PC"]],"adrian federico t.":[80,["P_INNO : Voyage d'\u00e9tude","P_SYS : Premier PC","P_WebDB : Mod\u00e9liser un site web"],0,[]],"mohammad dawood a.":[80,["P_INNO : Voyage d'\u00e9tude","P_SYS : Premier PC","P_WebDB : Analyse d'informations personnelles"],24,["P_WebDB : Mod\u00e9liser un site web"]],"mateen salem k.":[56,["P_INNO : Voyage d'\u00e9tude","P_WebDB : Mod\u00e9liser un site web"],24,["P_SYS : Premier PC"]],"abiram m.":[0,[],80,["P_INNO : Voyage d'\u00e9tude","P_SYS : Premier PC","P_WebDB : Mod\u00e9liser un site web"]],"younes abdelhamid c.":[32,["P_INNO : Voyage d'\u00e9tude"],24,["P_SYS : Premier PC"]],"evan dimitri f.":[56,["P_INNO : Voyage d'\u00e9tude","P_WebDB : Mod\u00e9liser un site web"],24,["P_SYS : Premier PC"]],"julien pierre m.":[80,["P_INNO : Voyage d'\u00e9tude","P_SYS : Premier PC","P_WebDB : Mod\u00e9liser un site web"],0,[]],"evin p.":[56,["P_INNO : Voyage d'\u00e9tude","P_WebDB : Mod\u00e9liser un site web"],24,["P_SYS : Premier PC"]]},"cin1a":{"all":[16,["lucas pierre nangawi k.","diego fernando m.","giovanni b.","luke raimondo m.","alan b.","teo l.","siem b.","nikola g.","mohamed zaahid m.","even gavri\u00ebl m.","rui pedro r.","william andres t.","amir z.","nima amir aram z.","dario jhesuanj c.","surenthar j."],0,[]],"lucas pierre nangawi k.":[32,["P_INNO : Voyage d'\u00e9tude"],0,[]],"diego fernando m.":[32,["P_INNO : Voyage d'\u00e9tude"],0,[]],"giovanni b.":[56,["P_INNO : Voyage d'\u00e9tude","P_WebDB : Analyse d'informations personnelles"],0,[]],"luke raimondo m.":[32,["P_INNO : Voyage d'\u00e9tude"],0,[]],"alan b.":[32,["P_INNO : Voyage d'\u00e9tude"],0,[]],"teo l.":[32,["P_INNO : Voyage d'\u00e9tude"],0,[]],"siem b.":[32,["P_INNO : Voyage d'\u00e9tude"],0,[]],"nikola g.":[32,["P_INNO : Voyage d'\u00e9tude"],0,[]],"mohamed zaahid m.":[32,["P_INNO : Voyage d'\u00e9tude"],0,[]],"even gavri\u00ebl m.":[32,["P_INNO : Voyage d'\u00e9tude"],0,[]],"rui pedro r.":[32,["P_INNO : Voyage d'\u00e9tude"],0,[]],"william andres t.":[32,["P_INNO : Voyage d'\u00e9tude"],0,[]],"amir z.":[32,["P_INNO : Voyage d'\u00e9tude"],0,[]],"nima amir aram z.":[32,["P_INNO : Voyage d'\u00e9tude"],0,[]],"dario jhesuanj c.":[32,["P_INNO : Voyage d'\u00e9tude"],0,[]],"surenthar j.":[32,["P_INNO : Voyage d'\u00e9tude"],0,[]]},"cin2b":{"all":[1,["nelson p."],1,["matej r."]],"nelson p.":[24,["P_WebDB : Mod\u00e9liser un site web"],0,[]],"matej r.":[0,[],24,["P_WebDB : Mod\u00e9liser un site web"]]}},"datesWindow":["2022-08-01 12:00","2023-07-31 12:00","2023-03-06 11:00","2023-06-11 11:00"],"groupsCount":4};--}}

    const datesWindow=allJsonData["datesWindow"];

    const summariesData = allJsonData["summaries"];
    const groupsForSummary = Object.keys(summariesData);
    let studentsCount =0;

    const summarySeries=[];
    const summaryTitles=[];


    const groupRadius=100;
    const groupHeight = groupRadius*2;
    let summaryTop=5;
    const groupWidth = groupRadius*2;
    const studentsTotalWidth=600;
    const studentsPack=8;
    const studentWidth = studentsTotalWidth/studentsPack;
    const horizontalSpacer1=10;//space between group and first student
    horizontalSpacer2=50;//space between students

    const verticalSpacer1=70;//y space between students

    const studentNameTitle=15;//y space for student title

    let maxTop = 0;

    groupsForSummary.forEach((groupName, idxGroup)=>{

        const studentsData = summariesData[groupName];
        const students = Object.keys(studentsData);
        let left=0;

        students.forEach((student,idxStudent)=>{

            const studentSummary = summariesData[groupName][student];

            let height=groupHeight;
            let width = groupWidth;

            let title=groupName;
            let radius = groupRadius*0.9;
            let top = summaryTop;
            let topTitle = summaryTop;

            let isGroup=true;
            if(student!="all"){
                isGroup=false;
                radius *=0.6;
                height=groupHeight;
                width=studentWidth;

                title=student.limit(10);

                studentsCount++;

                //first line of students, move x
                if(idxStudent==1){
                    left+=groupWidth+horizontalSpacer1;
                    summaryTop+=studentNameTitle;
                    top+=studentNameTitle;
                    topTitle=top;
                }//Move student top for second line ? (-1 for the group chart)
                else if(idxStudent!=1 && (idxStudent-1)%studentsPack==0){

                    summaryTop+=groupHeight/2+verticalSpacer1;

                    top=summaryTop;
                    topTitle=summaryTop;
                    left = groupWidth+horizontalSpacer1;


                }//move student to next x
                else{
                    left +=studentWidth+horizontalSpacer2;
                }

            }
            //Group
            else{
                //Adjust because x,y of pie is in the center :-(
                top+=groupsForSummary.length>1?groupRadius:groupRadius/2;//move group chart to center of student small charts

                maxTop = top;
            }

            //console.log(`${groupName}-${student}: top=${summaryTop}px, left=${left}px, radius=${radius}`);

            let total = studentSummary[0]+studentSummary[2];
            const successPercentage = studentSummary[0]/total;
            const isFailing =successPercentage<{{\App\Services\SummariesService::SUCCESS_REQUIREMENT}};
            let successColor = yellow;
            if(isGroup || !isFailing){
                successColor=green;
            }
            //const difference = Math.round(studentSummary[0]- Math.round(total*{{\App\Services\SummariesService::SUCCESS_REQUIREMENT}}));

            let target=0;
            if(isFailing){
                let a=studentSummary[0];
                let b=total;
                let c={{\App\Services\SummariesService::SUCCESS_REQUIREMENT}}*100;
                let d=100;
                //Chatgpt: So, the generic formula to solve the equation (a + x) / (b + x) = c / d is:   x = (cb - ad) / (d - c)
                target = Math.round((c*b - a*d) / (d - c));
            }

            summaryTitles.push({
                text: title,
                subtext: `${Math.round(successPercentage*100)}%`+ (isGroup||!isFailing?``:` (-${target}p)`),
                top: topTitle + 'px',
                left: left+'px',
            });

            //console.log(successColor);
            summarySeries.push({
                type: 'pie',
                radius:[radius,'15%'],
                top: top + 'px',
                height:height,
                left: left,
                width: width,
                label: {
                    //fontWeight:'bold',
                    position:'inside',
                    formatter: function(data){
                        let isGroup = data.data['isGroup'];
                        if(isGroup){
                            return `${data['value']} élève(s)`;
                        }
                        return `${data.data['value']}p\n(${data.data['details'].length} pr.)`;
                    },
                },
                color:[successColor,red],
                data: [
                    {name:'ok'+groupName+student,value:studentSummary[0],details:studentSummary[1],total:total,isGroup:isGroup},
                    {name:'ko'+groupName+student,value:studentSummary[2],details:studentSummary[3],total:total,isGroup:isGroup}
                ],
                /*
                itemStyle: {
                    borderRadius: 1,
                    borderColor: '#9d9999',
                    borderWidth: 1
                },*/
            });

        });
        summaryTop+=groupHeight;

        //when not much students...
        if( students.length-1<studentsPack){
            summaryTop+=groupHeight/2+verticalSpacer1;
        }

    });

    //For students, only show himself in the big graph
    if(summarySeries.length==2){
        summaryTitles[0].text = summaryTitles[1].text;
        summaryTitles[0].subtext = summaryTitles[1].subtext;
        summaryTitles.pop();

        summarySeries[0].data = summarySeries[1].data;
        summarySeries[0].color = summarySeries[1].color;
        summarySeries.pop();
    }

    const summariesChartOption = {
        title: summaryTitles,
        series: summarySeries,
        tooltip: {
            trigger: 'item',
            formatter:function(data){return data['data']['details'].join(',');}
        },

    };

    const evaluationsData = allJsonData["evaluations"];
    const groupsForEvaluation = Object.keys(evaluationsData);

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
        yAxis: {{\App\Services\SummariesService::SUCCESS_REQUIREMENT*100}},
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

    const grids = [];
    const xAxes = [];
    const yAxes = [];
    const series = [];
    const titles = [];
    const legends =[];
    const visualMaps =[];
    const dataZooms = [];
    let chartsIndex = 0;

    const successes = [];

    groupsForEvaluation.forEach((groupName)=>{

        const groupData = evaluationsData[groupName];
        const students = Object.keys(groupData);


        {{-- Build students informations --}}
        let studentLatestSuccessPercentage=0;
        students.forEach((student)=>{

            const studentSeriesData = groupData[student];
            {{-- compute markers to identify succes/failure points --}}
            const markPointData=[];

            studentSeriesData.forEach((pointData)=>{

                {{-- success --}}
                let color = green;
                let rotate=0;
                {{-- win percentage=0 -> FAILURE --}}
                if(pointData[PI_SUCCESS_TIME]==0){
                    color=red;
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

                studentLatestSuccessPercentage=pointData[PI_CURRENT_PERCENTAGE];
            });

            successes[student]=studentLatestSuccessPercentage>={{\App\Services\SummariesService::SUCCESS_REQUIREMENT}}*100;


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
                xAxisIndex: chartsIndex,
                yAxisIndex: chartsIndex,
                ...(students.length==1) && {endLabel: {
                        show: true,
                        formatter: function (params) {
                            return params.seriesName;
                        }
                    }}
            });

        });

        grids.push({
            show: true,
            borderWidth: 2,
            shadowColor: 'rgba(0, 0, 0, 0.3)',
            shadowBlur: 2
        });

        xAxes.push({
            type: 'time',
            gridIndex: chartsIndex,
            min:datesWindow[0],
            max:datesWindow[1]
        });
        yAxes.push({
            type: 'value',
            max: 100,
            gridIndex: chartsIndex
        });

        titles.push({
            //left:'center',
            textAlign: 'center',
            text: groupName,
            textStyle: {
                fontSize: 12,
                //fontWeight: 'normal'
            }
        });

        legends.push(
            (students.length<=1)?{show:false}:{
                data: students.map((stud)=>new Object({name:stud,icon: successes[stud]?'':'triangle',itemStyle:{borderColor:red,borderWidth:successes[stud]?0:2}})),
                orient:'horizontal',
                formatter: function(name){return name.limit(15);}
            },
        );
        visualMaps.push(
            (students.length>1)?{}:
                { // the first visualMap component
                    type: 'piecewise', // defined to be continuous visualMap
                    //seriesIndex: 0,
                    dimension:1,
                    color:[green,red],
                    min:0,
                    max:100,
                    show:false
                },
        );

        chartsIndex++;

    });


    const chartCounts = chartsIndex+1;
    const offsetForMainTitle = 10;
    const spaceBetweenCharts = 15;
    const availableForAllCharts = 100-(spaceBetweenCharts*chartCounts-1);
    const singleHeight = availableForAllCharts/chartCounts;

    let gridTop=offsetForMainTitle;

    {{-- Place elements on grid --}}
    grids.forEach(function (grid, idx) {
        grid.left='3%';

        grid.width='90%';

        grid.height= `${singleHeight}%`;
        grid.top = `${gridTop}%`;

        titles[idx].left = `${parseFloat(grid.left)+parseFloat(grid.width)/2}%`;
        titles[idx].top = `${parseFloat(grid.top)-8}%`;

        legends[idx].top = `${parseFloat(grid.top)-spaceBetweenCharts/2.5}%`;
        legends[idx].width = `${parseFloat(grid.width)}%`;
        legends[idx].left = `${parseFloat(grid.left)}%`;
        //legends[idx].left = `${parseFloat(grid.left)+parseFloat(grid.width)+2}%`;

        gridTop += singleHeight+spaceBetweenCharts;

    });

    // Specify the configuration items and data for the chart
    let evolutionChartOption = {
        title: titles.concat([
            {
                text: '{{__('Evolution')}}',
                top: 0,
                left: 0
            }
        ]),
        grid: grids,
        xAxis: xAxes,
        yAxis: yAxes,
        series:series,
        //series: series,

        legend:legends,
        visualMaps:visualMaps,

        tooltip: {
            trigger: "axis",
            formatter: function (params) {

                const pointData=params[0].data;{{-- with axis trigger adds an array dimension for multiple y on same x... --}}
                const globalInfo =
                    `{{__('Project')}}: <b>${pointData[PI_PROJECT_NAME]}</b> (${pointData[PI_CLIENTS]})<br />`+
                    `{{__('Date')}}: ${new Date(pointData[PI_DATE]).toLocaleDateString()}<br />`;

                {{-- Only if not multiple points...--}}
                if(params.length ==1){
                    return `${params[0].seriesName}<br />` + globalInfo +
                        `{{__('Result')}}: ${pointData[PI_SUCCESS_TIME]}/${pointData[PI_TIME]}p<br />`+
                        `{{__('Current summary')}}: ${pointData[PI_CURRENT_PERCENTAGE]}%`;
                }

                let compiledInfos={success:[],failure:[]};

                params.forEach(function(data){
                   const studentName = data.seriesName;
                   const pointData = data.data;
                   const ok = pointData[PI_SUCCESS_TIME]>0;
                   compiledInfos[ok?"success":"failure"].push(studentName);

                });

                return globalInfo+'<br/>ok:'+compiledInfos["success"].join('<br />')+'<br/><br/>ko:'+compiledInfos["failure"].join('<br />');

            },

        },
        dataZoom:
            {
                type: 'slider',
                xAxisIndex:grids.map((_,i)=>i),
                startValue: datesWindow[2],
                endValue: datesWindow[3],
                top:parseFloat(grids[grids.length-1].top)+parseFloat(grids[grids.length-1].height)+4+(grids.length==1?10:0)+'%'
            },

    };


    //Create chart
    let evolutionChart = echarts.init(document.getElementById('evolutionCharts'), theme, {
        //width: '100%',
        height: 285*groupsForEvaluation.length
    });
    evolutionChart.setOption(evolutionChartOption);

    //Create chart
    let summariesChart = echarts.init(document.getElementById('summariesCharts'), theme, {
        //width: '100%',
        height: maxTop+groupRadius*3
    });
    summariesChart.setOption(summariesChartOption);

    window.addEventListener('resize', function() {
        [evolutionChart,summariesChart].forEach(chart=>chart.resize());
    });
</script>
