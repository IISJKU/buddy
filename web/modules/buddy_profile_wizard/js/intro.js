class Intro extends Phaser.Scene {

  constructor ()
  {
    super('Intro');
  }

  preload() {
    this.load.plugin('rexglowfilterpipelineplugin', 'https://raw.githubusercontent.com/rexrainbow/phaser3-rex-notes/master/dist/rexglowfilterpipelineplugin.min.js', true);
    this.load.plugin('rexinversepipelineplugin', 'https://raw.githubusercontent.com/rexrainbow/phaser3-rex-notes/master/dist/rexinversepipelineplugin.min.js', true);
    this.cameras.main.backgroundColor = Phaser.Display.Color.HexStringToColor("#3498db");
    this.load.image('yesIcon', 'modules/buddy_profile_wizard/assets/img/util/NoIcon.png');
    this.load.image('noIcon', 'modules/buddy_profile_wizard/assets/img/util/YesIcon.png');
    this.load.image('textToSpeech', 'modules/buddy_profile_wizard/assets/img/util/TextToSpeech.png');
    this.load.audio('no', 'modules/buddy_profile_wizard/assets/sounds/no.wav');
  }


  create ()
  {
    this.coolSound = this.sound.add('no');


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

    this.test = new TextButton(this,"test \n test",400 ,100,function (){
      console.log("OARSCH");
    });
    this.test.init();
    this.add.existing(this.test);


    this.test2 = new IconButton(this,"test test",200 ,300,"yesIcon",function (){
      console.log("OARSCH2");
    });
    this.test2.init();
    this.add.existing(this.test2);


    this.test3 = new TextToSpeechButton(this,"test test",400 ,300,this.coolSound,function (){
      console.log("OARSCH2");
    });
    this.test3.init();
    this.add.existing(this.test3);

    //this.background = this.add.rectangle(11, 11, 20 , 20, 0xffffff);

  }

  updateClickCountText() {

    console.log("CLICKY");
  }
}
