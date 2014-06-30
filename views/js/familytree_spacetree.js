			var labelType, useGradients, nativeTextSupport, animate, st;

			function view_member(id) {
				$.ajaxSetup ({ cache: false });
				var message = "جاري التحميل...";
				$("#familytree_leftinfo").html(message).load("control.php?do=view_member&tribe_id={tribe_id}&id=" + id);
				$("#familytree_leftinfo").css("overflow-y", "scroll");
			}

			function delete_member(id) {
				$.ajaxSetup ({ cache: false });
				var message = "جاري التحميل...";
				$("#familytree_leftinfo").html(message).load("control.php?do=delete_member&tribe_id={tribe_id}&id=" + id);
				$("#familytree_leftinfo").css("overflow-y", "hidden");
			}

			$(function() {
				var ua = navigator.userAgent,
				iStuff = ua.match(/iPhone/i) || ua.match(/iPad/i),
				typeOfCanvas = typeof HTMLCanvasElement,
				nativeCanvasSupport = (typeOfCanvas == 'object' || typeOfCanvas == 'function'),
				textSupport = nativeCanvasSupport && (typeof document.createElement('canvas').getContext('2d').fillText == 'function');
				labelType = (!nativeCanvasSupport || (textSupport && !iStuff))? 'Native' : 'HTML';
				nativeTextSupport = labelType == 'Native'; useGradients = nativeCanvasSupport;
				animate = !(iStuff || !nativeCanvasSupport);
				init();
			});

			function init(){

				var json = {familytree};
	
				st = new $jit.ST({
					injectInto: 'infovis',
					duration: 200,
					transition: $jit.Trans.Quart.easeInOut,
					levelDistance: 80,
					Navigation: { enable:true, panning:true },
					Node: { autoHeight: true, autoWidth: true, type: 'rectangle', color: '#BDDF96', overridable: true, align: "center" },
					Edge: { type: 'bezier', overridable: true, color: '#ccc', lineWidth: 2 },
					orientation: "left",
					onBeforeCompute: function(node){
						view_member(node.id);
						$(".loading").show("fast");
					},
					onAfterCompute: function(){
						$(".loading").hide("fast");
					},
					onCreateLabel: function(label, node){
						label.id = node.id;
						label.innerHTML = node.name;
						label.onclick = function(){
							st.onClick(node.id);
						};
						
						var style = label.style;
						style.cursor = 'pointer';
						style.color = '#333';
						style.textAlign= 'center';
						style.padding = '2px';
					},
					onBeforePlotNode: function(node){
						if (node.selected){
							node.data.$color = "#ff7";
						}else{
							delete node.data.$color;
				
							if(!node.anySubnode("exist")) {
								var count = 0;
					
								node.eachSubnode(function(n) {
									count++;
								});

								if (count > 5){
									node.data.$color = '#FADBEC';
								}else{
									node.data.$color = ['#FFA760', '#C3E7F3', '#EDAED0', '#DCF0C5', '#FFD3B0', '#E6F8FE'][count];
								}
							}
						}
					},
					onBeforePlotLine: function(adj){
						if (adj.nodeFrom.selected && adj.nodeTo.selected) {
							adj.data.$color = "#eed"; adj.data.$lineWidth = 3;
						}else{ 
							delete adj.data.$color;
							delete adj.data.$lineWidth;
						}
					}
				}); 

				st.loadJSON(json); st.compute();
				st.geom.translate(new $jit.Complex(-200, 0), "current");
				st.onClick("{id}");
			}
