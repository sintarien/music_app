<script src="https://cdnjs.cloudflare.com/ajax/libs/wavesurfer.js/3.0.0/wavesurfer.min.js"></script>
<div class="container">
	<div class="col-md-12">
		<table class="table table-hover">
			<thead>
				<tr>
					<th> </th>
				</tr>
			</thead>
			<tbody id="musicList">
			</tbody>
		</table>
	</div>
	<div musicid="46"></div>
	<form id="audioForm">
	  <input id="audio_file" type="file" name="myfile" />
	</form>
	<button id="file_upload" class="">UPLOAD</button>
	<button id="test" class="">test</button>
	<img id="imgTest"></img>
	<div id="waveform"> </div>
	<div style="text-align: center">
		<div id="menu">
			<button id="btnStartPause" class="menubutton">start/pause</button>
		</div>
		<p class="row">
			<div class="col-xs-1">
				<i class="glyphicon glyphicon-zoom-in"></i>
			</div>
			<div class="col-xs-10">
				<input id="slider" type="range" min="1" max="200" value="1" style="width: 100%" />
			</div>
			<div class="col-xs-1">
				<i class="glyphicon glyphicon-zoom-out"></i>
			</div>
		</p>
	</div>
</div>

<script type="text/javascript">
	var wavesurfers = [];
	$(function(){
		var musicList = new AjaxMng("/music/list.json","GET","");
		/*
		musicList.exec((data)=>{
			console.log(data);
			$.each(data.json.musics,function(index,value){
				console.log(value.id);
				var img = $('<img>').attr('src','/musicapp/img/ueda.png')
					.attr('width','40').attr('height','40');
				var playStop = $('<button>').attr('type','button')
					.addClass('btn btn-primary playstop').attr('musicid',value.id).html('PLAY/STOP');
				var bookmark = $('<button>').attr('type','button')
					.addClass('btn btn-info').html('BOOK MARK');

				$('#musicList').append(
					$('<tr>').append(
						$('<td>').append(
							$('<div>').addClass('row')
								.append($('<div>').append(img).addClass('col-md-1'))
								.append($('<div>').attr('musicid',value.id).addClass('col-md-9 wave'))
								.append($('<div>').append(playStop).append(bookmark).addClass('col-md-2'))
						)
					)
				)

				waveGetter(value.id);
			});
		});
		*/
		/*
		$(document).on("click",".playstop",function(){
					var musicid = $(this).attr('musicid');
					wavesurfers[musicid].playPause();
		});
		*/
	});

/*
		$("#file_upload").click(function () {
			var formdata = new FormData($('#audioForm').get(0));
			$.ajax({
				url  : "/money/api/music/upload.json",
				type : "POST",
				data : formdata,
				cache       : false,
				contentType : false,
				processData : false
			}).done(function (data) {
				alert("success");
			}).fail(function() {
			// 失敗時の処理
			});
		});
*/
/*
function waveGetter(musicid){
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function(){
		if (this.readyState == 4 && this.status == 200) {
			console.log(this.response);
			var file = new window.Blob([this.response]);
			var wavesurfer = WaveSurfer.create({
				container: 'div[musicid="' + musicid + '"]',
				waveColor: 'hotpink',
				barWidth: 1,
		    progressColor: 'orange',
			});
			wavesurfer.loadBlob(file);
			wavesurfers[musicid] = wavesurfer;
		}
	}
	xhr.open('GET', "/money/api/music/" + musicid + "/get.json");
	xhr.responseType = 'blob';
	xhr.send();
}
*/
function test(musicid){
	console.log(1234);
	var wavesurfer = WaveSurfer.create({
		container: 'div[musicid="' + musicid + '"]',
		waveColor: 'hotpink',
		barWidth: 1,
		progressColor: 'orange',
	});
	var url = "/money/api/music/" + musicid + "/get.json";
	var musicData = "";
	window.fetch(url)
    .then(function(response) {
			console.log("hoge11");
			//console.log(response.blob());
      return response.blob();
    })
    .then(function(blob) {
			console.log("hoge21");
			//console.log(blob);
      readBlobAsText(blob, 65536, function(state, data, offset) {
				console.log(1);
				musicData += data;
				//console.log(musicData);
				console.log(2);
				var file = new window.Blob([musicData],{ type: 'audio/mpeg' });
				console.log(3);
				console.log(file)
				wavesurfer.load(musicData);
				console.log(4);

    });
  });
}
function readBlobAsText(blob, size, callback) {
  var offset = 0;
  var reader = new FileReader();
  reader.onload = function(e) {
		console.log("onload");
    callback(e.target.readyState, e.target.result, offset);
    if (offset < blob.size) {
      slice = blob.slice(offset, offset + size, blob.type);
      offset += size;
      reader.readAsBinaryString(slice);
    }
  }
  reader.onerror = function(e) {
    console.log("error=" + e.target.error.name);
  }
	console.log(blob.type);
  slice = blob.slice(offset, offset + size, blob.type);
  offset += size;
  reader.readAsBinaryString(slice);
}

$(document).on("click","#test",function(){
			console.log(456);
			test(46);
});

</script>
