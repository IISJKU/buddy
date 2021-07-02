class GameScene extends Phaser.Scene {
  constructor(id) {
    super(id);
  }

  preload() {
    this.load.plugin('rexglowfilterpipelineplugin', 'https://raw.githubusercontent.com/rexrainbow/phaser3-rex-notes/master/dist/rexglowfilterpipelineplugin.min.js', true);
    this.load.plugin('rexinversepipelineplugin', 'https://raw.githubusercontent.com/rexrainbow/phaser3-rex-notes/master/dist/rexinversepipelineplugin.min.js', true);
    this.cameras.main.backgroundColor = Phaser.Display.Color.HexStringToColor("#3498db");
    this.load.image('yesIcon', 'modules/buddy_profile_wizard/assets/img/util/NoIcon.png');
    this.load.image('noIcon', 'modules/buddy_profile_wizard/assets/img/util/YesIcon.png');
    this.load.image('retryIcon', 'modules/buddy_profile_wizard/assets/img/util/RetryIcon.png');
    this.load.image('playIcon', 'modules/buddy_profile_wizard/assets/img/util/PlayIcon.png');
    this.load.image('ffIcon', 'modules/buddy_profile_wizard/assets/img/util/FastForwardIcon.png');
    this.load.image('fbIcon', 'modules/buddy_profile_wizard/assets/img/util/FastBackIcon.png');
    this.load.image('textToSpeech', 'modules/buddy_profile_wizard/assets/img/util/TextToSpeech.png');
    this.load.audio('no', 'modules/buddy_profile_wizard/assets/sounds/no.wav');
  }

  createTitle(title, style, sound) {
    console.log(title);
    if (!style) {
      style = {
        fontFamily: 'Arial Black',
        fontSize: 40,
        fontStyle: "bold",
        stroke: '#000000',
        strokeThickness: 5,
        fill: '#ffffff',
      };
    }

    this.titleText = this.add.text(this.cameras.main.centerX, 0, title, style).setOrigin(0.5, 0);


  }

  hideTitle() {

    if (this.titleText) {
      this.titleText.visible = false;
    }
  }

  showTitle() {
    if (this.titleText) {
      this.titleText.visible = true;
    }
  }


}


class QuizScene
  extends GameScene {
  constructor(id) {
    super(id);
    this.quizQuestions = [];
    this.timeLimit = 0;

  }

  preload() {
    super.preload();

  }


  renderQuestion(question) {

    let heading = this.renderQuestionHeading(question.question, question.illustration);

    for (let i = 0; i < question.answers.length; i++) {

      let answer = this.renderAnswer(question.answers[i]);
    }


  }

  renderQuestionHeading(question, illustration) {

  }

  renderAnswer(answer) {


  }

}
