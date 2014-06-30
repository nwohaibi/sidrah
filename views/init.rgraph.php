		    //init RGraph
		    var rgraph = new $jit.RGraph({
		        //Where to append the visualization
		        injectInto: 'infovis',
		        //Optional: create a background canvas that plots
		        //concentric circles.
		        background: {
		          CanvasStyles: {
		            strokeStyle: '#ccc'
		          }
		        },
		        //Add navigation capabilities:
		        //zooming by scrolling and panning.
		        Navigation: {
		          enable: true,
		          panning: true,
		          zooming: 50
		        },
		        //Set Node and Edge styles.
		        Node: {
		            color: '#c93383',
		        },
		        
		        Edge: {
		          color: '#70b6cd',
		          lineWidth:1.5
		        },

		        onBeforeCompute: function(node){
			$.ajaxSetup ({  
				cache: false  
			}); 
		            
		            var message = "جاري التحميل...";
			$("#info_content").html(message).load("member.php?id=" + node.id);
		        },
		        
		        //Add the name of the node in the correponding label
		        //and a click handler to move the graph.
		        //This method is called once, on label creation.
		        onCreateLabel: function(domElement, node){
		            domElement.innerHTML = node.name;
		            domElement.onclick = function(){
			   rgraph.onClick(node.id, {
			       onComplete: function() {
			           //Log.write("done");
			       }
			   });
		            };
		        },
		        //Change some label dom properties.
		        //This method is called each time a label is plotted.
		        onPlaceLabel: function(domElement, node){
		            var style = domElement.style;
		            style.display = '';
		            style.cursor = 'pointer';

		            if (node._depth < 1) {
			   style.fontSize = "14pt";
			   style.color = "#000";
		            
		            }else if ( node._depth == 1){
			   style.fontSize = "10pt";
			   style.color = "#000";
		            
		            }else{
			   style.fontSize = "8pt";
			   style.color = "#888";
		            }

		            var left = parseInt(style.left);
		            var w = domElement.offsetWidth;
		            style.left = (left - w / 2) + 'px';
		        }
		    });
		    //load JSON data
		    rgraph.loadJSON(json);
		    
		    //trigger small animation
		    rgraph.graph.eachNode(function(n) {
		      var pos = n.getPos();
		      pos.setc(-200, -200);
		    });
		    
		    rgraph.compute('end');
		    rgraph.fx.animate({
		      modes:['polar'],
		      duration: 2000
		    });
		    //end
		    
		    rgraph.onClick("{id}");
