class WritingGame extends GameScene {

  constructor() {
    super('WritingGame');
    this.text = null;
    this.delay = 1000;
    this.personScale = 0.55;
    this.personWidth = 100;
    this.steps = [];
  }

  preload() {
    super.preload();
    this.cameras.main.backgroundColor = Phaser.Display.Color.HexStringToColor("#3498db");
    this.load.image('house', 'modules/buddy_profile_wizard/assets/img/house.png');
    this.load.image('girl', 'modules/buddy_profile_wizard/assets/img/girl.png');
    this.load.image('boy', 'modules/buddy_profile_wizard/assets/img/boy.png');
    this.load.audio('drumLoop', 'modules/buddy_profile_wizard/assets/sounds/drumLoop.wav');
    this.load.audio('explosion', 'modules/buddy_profile_wizard/assets/sounds/drum.wav');
    this.load.audio('yes', 'modules/buddy_profile_wizard/assets/sounds/yes.wav');
    this.load.audio('no', 'modules/buddy_profile_wizard/assets/sounds/no.wav');

    this.load.audio('math_intro', soundFactory.getSound("math_game", "math_intro.mp3"));

    this.load.html('nameform', 'modules/buddy_profile_wizard/assets/html/inputform.html');
  }

  create() {


    let mathGame = this;
    this.createTitle(stringFactory.getString("math_game_title_1"));

    this.avatarButton = new AvatarAudioButton(this, "math_intro", this.cameras.main.centerX, 180, function () {

    });
    this.avatarButton.init();
    this.add.existing(this.avatarButton);


    this.startButton = new IconButton(this, stringFactory.getString("math_game_start"), this.cameras.main.centerX, 300, "playIcon", function () {
      mathGame.titleText.destroy();
      mathGame.startButton.destroy();
      mathGame.avatarButton.destroy();
      mathGame.startGame();
    });
    this.startButton.init();
    this.add.existing(this.startButton);


  }

  startGame(){

    this.avatarButton = new AvatarAudioButton(this, "math_intro", this.cameras.main.centerX, 80, function () {

    });
    this.avatarButton.init();
    this.add.existing(this.avatarButton);


    this.element = this.add.dom(400, 600).createFromCache('nameform');
    this.element.setPerspective(800);
    this.tweens.add({
      targets: this.element,
      y: 300,
      duration: 3000,
      ease: 'Power3'
    });

    document.getElementById("er_wizzard_text_input").focus();


    this.answerButton = new IconButton(this, stringFactory.getString("math_game_start"), this.cameras.main.centerX, 500, "playIcon", function () {

    });
    this.answerButton.init();
    this.add.existing(this.answerButton);
  }


}
