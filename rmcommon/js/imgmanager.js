function delete_size(e){var l=$(e).attr("id").substring(7),i=$("#sizes-container-all .single-size");return i.length<=1?void 0:($("#single-size-"+l).remove(),!1)}$(document).ready(function(){$(".new-size-button").click(function(){var table=$("#sizes-container-all .single-size:last"),id=table.attr("id").replace("single-size-",""),newid=id;newid++;var clone=table.clone();clone.attr("id","single-size-"+newid),html=eval("clone.html().replace(/sizes\\["+id+'\\]/g, "sizes['+newid+']");'),html=html.replace("delete-"+id,"delete-"+newid),html=html.replace(/value="*"/g,'value=""'),clone.html(html),clone.insertAfter(table),clone.effect("highlight",{})})});
