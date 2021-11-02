class WritingGame extends GameScene {

  constructor() {
    super('WritingGame');
    this.text = null;
    this.delay = 1000;
    this.personScale = 0.55;
    this.personWidth = 100;
    this.steps = [];
    this.currentStep = 0;
  }

  preload() {
    this.stringTokenizer = require('string-punctuation-tokenizer');
    super.preload();
    this.cameras.main.backgroundColor = Phaser.Display.Color.HexStringToColor("#3498db");

    this.load.audio('writing_game_text_intro', soundFactory.getSound("writing_game", "writing_game_intro.mp3"));
    console.log(soundFactory.getSound("writing_game", "writing_game_intro.mp3"));
    this.load.html('nameform', 'modules/buddy_profile_wizard/assets/html/inputform.html');

    this.steps.push("writing_game_sentence_1");
    this.steps.push("writing_game_sentence_2");
    this.steps.push("writing_game_sentence_3");

    for(let i=0; i < this.steps.length; i++){

      this.load.audio(this.steps[i], soundFactory.getSound("writing_game", this.steps[i]+".mp3"));
    }

  }

  create() {


    let mathGame = this;
    this.createTitle(stringFactory.getString("writing_game_text_intro"));

    this.avatarButton = new AvatarAudioButton(this, "writing_game_text_intro", this.cameras.main.centerX, 180, function () {

    });
    this.avatarButton.init();
    this.add.existing(this.avatarButton);


    this.startButton = new IconButton(this, stringFactory.getString("math_game_start"), this.cameras.main.centerX, 300, "playIcon", function () {
      mathGame.titleText.destroy();
      mathGame.startButton.destroy();
      mathGame.avatarButton.destroy();
      mathGame.nextStep();
    });
    this.startButton.init();
    this.add.existing(this.startButton);


  }

  nextStep(){

    if(this.currentStep < this.steps.length){

      this.avatarButton = new AvatarAudioButton(this, this.steps[this.currentStep], this.cameras.main.centerX, 80, function () {

      });
      this.avatarButton.init();
      this.add.existing(this.avatarButton);


      this.element = this.add.dom(400, 300).createFromCache('nameform');
      this.element.setPerspective(800);
      /*
      this.tweens.add({
        targets: this.element,
        y: 300,
        duration: 3000,
        ease: 'Power3'
      });*/

      document.getElementById("er_wizzard_text_input").focus();


      let writingGame = this;
      this.answerButton = new IconButton(this, stringFactory.getString("math_game_start"), this.cameras.main.centerX, 500, "playIcon", function () {
        let input = document.getElementById("er_wizzard_text_input").value;


        writingGame.evaluateInput(input);



      });
      this.answerButton.init();
      this.add.existing(this.answerButton);
    }



  }

  cleanUpStep(){

  }

  evaluateInput(text){

    let spokenText = stringFactory.getString(this.steps[this.currentStep]);
    let textSimilarity = stringSimilarity.compareTwoStrings(text, spokenText)




    let words = this.stringTokenizer.tokenize ({text: text});
    let spokenWords = this.stringTokenizer.tokenize ({text: spokenText})

    let maxComparisons = Math.min(spokenWords.length, words.length);

    let wordSimularity = 0;
    for(let i=0; i < maxComparisons; i++){

      wordSimularity+=this.spellCompareWords(words[i],spokenWords[i]);

    }

    //Compare words
    wordSimularity = wordSimularity/Math.max(spokenWords.length, words.length);

    console.log(textSimilarity,wordSimularity);
    let textSkillLevel = Math.max(textSimilarity,wordSimularity);
    console.log("Your skill level:"+textSkillLevel);

    return textSkillLevel;


  }

  spellCompareWords(word1,word2){
    let similarity = stringSimilarity.compareTwoStrings(word1, word2);

    if(similarity < 0.8){
      let soundex1= soundex(word1);
      let soundex2 = soundex(word2);

      //if they sound alike set similarity to 0.7
      if(soundex1 === soundex2){
        similarity = 0.8;
      }
    }
    return similarity;
  }


}
