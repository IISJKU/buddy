class Intro extends Phaser.Scene {

  constructor ()
  {
    super('Intro');
  }

  preload() {
    this.cameras.main.backgroundColor = Phaser.Display.Color.HexStringToColor("#3498db");

  }


  create ()
  {

    /*
    this.add.text(
      640,
      360,
      "Hello World",
      {
        fontSize: 50,
        color: "#000000",
        fontStyle: "bold"
      }
    ).setOrigin(0.5);

    this.clickCount = 0;
    this.clickCountText = this.add.text(100, 200, '');

    this.clickButton = new TextButton(this, 100, 100, 'Click me!', () => this.updateClickCountText());
    this.add.existing(this.clickButton);

    */

    this.test = new MyButton(this,"test \n test",400 ,100,function (){
      console.log("OARSCH");
    });
    this.add.existing(this.test);

    //this.background = this.add.rectangle(11, 11, 20 , 20, 0xffffff);

  }

  updateClickCountText() {

    console.log("CLICKY");
  }
}
