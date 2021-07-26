class Intro extends GameScene {

  constructor ()
  {
    super('Intro');
  }

  preload() {

    super.preload();
    this.load.audio('intro_message', 'modules/buddy_profile_wizard/assets/sounds/intro/de/intro.mp3');
  }


  create ()
  {

    this.coolSound = this.sound.add('no');

    this.createTitle(stringFactory.getString("intro_title"));

    this.avatarButton = new AvatarAudioButton(this,"intro_message",this.cameras.main.centerX, 180,function (){

    });
    this.avatarButton.init();
    this.add.existing(this.avatarButton);


    this.startButton = new IconButton(this,stringFactory.getString("intro_start_game"),this.cameras.main.centerX, 300,"playIcon",function (){

      Director.changeScene("Intro");

      //game.scene.stop("Intro");
      //game.scene.start("MathGame");
    });
    this.startButton.init();
    this.add.existing(this.startButton);


    /*

    let test = this;
    this.test = new TextButton(this,"test \n test",400 ,100,function (){
      console.log("OARSCH");
      test.scale.startFullscreen();
    });
    this.test.init();
    this.add.existing(this.test);


    this.test2 = new IconButton(this,"test test",200 ,300,"yesIcon",function (){
      console.log("OARSCH2");
    });
    this.test2.init();
    this.add.existing(this.test2);


    this.test3 = new TextToSpeechButton(this,"test test",400 ,300,this.coolSound,function (){
      game.scene.stop("Intro");
      game.scene.start("MathGame");
    });
    this.test3.init();
    this.add.existing(this.test3);

    */

  }

  updateClickCountText() {

    console.log("CLICKY");
  }
}
