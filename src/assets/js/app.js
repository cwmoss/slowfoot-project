$('document').ready(function() {
	console.log("init")
// 250 × 32 is "couldn't get image"
	$('img.maybenot').on('load', function(){
		// console.log('loaded', this)
		if(this.width!=250 && this.height != 32){
			$(this).css("display", "block")
		}
	}).each(function(){
		// console.log("img", this)
		$(this).prop("src", $(this).data("src"))
	})

})

/*
const img = new Image();
img.onload = function() {
	alert(this.width + 'x' + this.height);
}
img.src = 'http://....';
*/