class MathGame extends Phaser.Scene {

  constructor() {
    super('MathGame');
    this.text = null;
    this.delay = 1000;
    this.personScale = 0.55;
    this.personWidth = 100;
    this.steps = [];
  }

  preload() {
    this.cameras.main.backgroundColor = Phaser.Display.Color.HexStringToColor("#3498db");
    this.load.image('house', 'modules/buddy_profile_wizard/assets/img/house.png');
    this.load.image('girl', 'modules/buddy_profile_wizard/assets/img/girl.png');
    this.load.image('boy', 'modules/buddy_profile_wizard/assets/img/boy.png');
    this.load.audio('drumLoop', 'modules/buddy_profile_wizard/assets/sounds/drumLoop.wav');
    this.load.audio('explosion', 'modules/buddy_profile_wizard/assets/sounds/drum.wav');
    this.load.audio('yes', 'modules/buddy_profile_wizard/assets/sounds/yes.wav');
    this.load.audio('no', 'modules/buddy_profile_wizard/assets/sounds/no.wav');
  }

  create() {




    let explosion = this.sound.add('explosion');
    this.drumloop = this.sound.add('drumLoop');
    this.yesSound = this.sound.add('yes');
    this.noSound = this.sound.add('no');

    this.setupGame();
    /*


    sprite.on("pointerdown", function (pointer) {

      console.log(pointer);
      explosion.play();
      game.scene.stop("MathGame");
      game.scene.start("MemoryGame");
    });
    */


  }

  setupGame(){
    this.text = this.add.text(
      this.cameras.main.centerX,
      50,
      "Count the people in the house",
      {
        fontSize: 30,
        color: "#000000",
        fontStyle: "bold"
      }).setOrigin(0.5);


    this.startText = this.add.text(
      this.cameras.main.centerX,
      95,
      "Start Game!",
      {
        fontSize: 25,
        color: "#000000",
        fontStyle: "bold",
      }).setOrigin(0.5);
    this.startText.setAlpha(0);

    this.startText.setInteractive(new Phaser.Geom.Rectangle(0, 0, this.text.width, this.text.height), Phaser.Geom.Rectangle.Contains);

    let mathGame = this;
    this.startText.on("pointerdown", function (pointer) {
      mathGame.startText.destroy();
      mathGame.text.destroy();
      mathGame.startGame();

    });

    this.time.addEvent({
      delay: 1000,
      callback: function () {
        mathGame.startText.setAlpha(1);


      }
    });


  }


  startGame() {
    let numberOfSteps = 5;
    let minPeople = 1;
    let maxPeople = 5;
    let currentPeople = 0;
    for (let i = 0; i < numberOfSteps; i++) {


      let operation = 0;
      if (currentPeople > 0) {
        operation = Math.floor(Math.random() * 2);
      }

      let maximum = maxPeople - minPeople;
      if (operation === 1 && maximum > currentPeople) {
        maximum = currentPeople;
      }
      let amount = Math.floor(Math.random() * maximum) + minPeople;

      if (operation === 0) {
        currentPeople += amount;
      } else {
        currentPeople -= amount;
      }


      this.steps.push({
        amount: amount,
        operation: operation
      });


    }

    this.finalAmount = currentPeople;

    this.houseSprite = this.add.sprite(this.cameras.main.centerX, -400, "house").setInteractive();
    this.houseSprite.setScale(0.5);

    let mathGame = this;
    let tween = this.tweens.add({
      targets: this.houseSprite,
      props: {
        y: {value: this.cameras.main.centerY, duration: 1000, ease: 'Bounce.easeOut'}
      },
      onComplete: function (tween, targets) {
        mathGame.nextStep();
      }
    });


  }

  nextStep() {


    if (this.steps.length > 0) {

      if (this.steps[0].operation === 0) {

        this.insertPeople(this.steps[0].amount, this.delay, true);
      } else {
        this.removePeople(this.steps[0].amount, this.delay, true);
      }

      this.steps.shift();
    } else {
      let mathGame = this;
      mathGame.time.addEvent({
        delay: 1000,
        callback: function () {


          mathGame.showQuiz();

        }
      });
      console.log("Final amount:" + this.finalAmount);
    }



  }

  insertPeople(amount, delay, triggerNextStep = true) {
    this.drumloop.play({loop: true});
    let speed = 5;
    let currentX = 0;
    let mathGame = this;
    let persons = [];
    let startX = 0;
    for (let i = 0; i < amount; i++) {
      let distance = (this.cameras.main.centerX - startX);
      let person = this.add.sprite(startX - this.personWidth * this.personScale, this.cameras.main.centerY + 45, this.getRandomAssetName());
      person.setScale(this.personScale);
      person.setDepth(-1);
      persons.push(person);


      let tween = this.tweens.add({
        targets: person,
        props: {
          x: {
            value: this.cameras.main.centerX,
            duration: distance * speed,
            delay: i * this.personScale * this.personWidth * speed,
          }
        }
      });

      if (i === amount - 1 && triggerNextStep) {
        tween.addListener("complete", function (tween, targets) {

          mathGame.drumloop.stop();
          mathGame.time.addEvent({
            delay: delay,
            callback: function () {

              for (let i = 0; i < persons.length; i++) {
                persons[i].destroy();
              }
              mathGame.nextStep();

            }
          });
        });
      }
    }
  }

  removePeople(amount, delay, triggerNextStep = true) {
    this.drumloop.play({loop: true});

    let speed = 5;
    let currentX = 0;
    let mathGame = this;
    let persons = [];
    for (let i = 0; i < amount; i++) {
      let distance = this.cameras.main.width - this.cameras.main.centerX;
      let person = this.add.sprite(this.cameras.main.centerX, this.cameras.main.centerY + 45, this.getRandomAssetName());
      person.setScale(this.personScale);
      person.setDepth(-1);
      persons.push(person);

      let tween = this.tweens.add({
        targets: person,
        props: {
          x: {
            value: this.cameras.main.width + this.personWidth * this.personScale,
            duration: distance * speed,
            delay: i * this.personScale * this.personWidth * speed,
          }
        },
      });

      if (i === amount - 1 && triggerNextStep) {
        tween.addListener("complete", function (tween, targets) {

          mathGame.drumloop.stop();

          mathGame.time.addEvent({
            delay: delay,
            callback: function () {

              for (let i = 0; i < persons.length; i++) {
                persons[i].destroy();
              }
              mathGame.nextStep();

            }
          })
        });
      }

    }
  }


  showQuiz(){
    let mathGame = this;
    this.text = this.add.text(
      this.cameras.main.centerX,
      50,
      "How many people are in the house?",
      {
        fontSize: 30,
        color: "#000000",
        fontStyle: "bold"
      }).setOrigin(0.5);


    let answers = [];
    answers.push(this.finalAmount);

    while(answers.length < 8){

      let amount = Math.floor(Math.random() * (this.finalAmount+10));
      if(!answers.includes(amount)){

        answers.push(amount);
      }

    }

    answers = this.shuffle(answers);


    let answerButtons = [];
    for(let i=0; i < answers.length; i++){

      let yPosition = 100;

      if(i < answers.length/2){
        yPosition = 150;
      }

      let position = i%(answers.length/2);


      let xPosition = position*this.cameras.main.width/(answers.length/2)+this.cameras.main.width/(answers.length);

      let answerButton = this.add.text(
        xPosition,
        yPosition,
        answers[i],
        {
          fontSize: 25,
          color: "#000000",
          fontStyle: "bold",
        }).setOrigin(0.5);
      answerButton.setInteractive(new Phaser.Geom.Rectangle(0, 0, answerButton.width, answerButton.height), Phaser.Geom.Rectangle.Contains);
      answerButtons.push(answerButton);

      answerButton.on("pointerdown", function (pointer) {

        if(answers[i]=== mathGame.finalAmount){

          mathGame.yesSound.play();

          for(let k=0; k<answerButtons.length; k++){
            answerButtons[k].destroy();
            mathGame.text.destroy();
            mathGame.houseSprite.destroy();
            mathGame.setupGame();
          }

        }else{
          mathGame.noSound.play();
        }
        console.log(answers[i]=== mathGame.finalAmount);

      });

    }




    /*
    this.startText = this.add.text(
      this.cameras.main.centerX,
      95,
      "Start Game!",
      {
        fontSize: 25,
        color: "#000000",
        fontStyle: "bold",
      }).setOrigin(0.5);
    this.startText.setAlpha(0);

    this.startText.setInteractive(new Phaser.Geom.Rectangle(0, 0, this.text.width, this.text.height), Phaser.Geom.Rectangle.Contains);

    let mathGame = this;
    this.startText.on("pointerdown", function (pointer) {
      mathGame.startText.destroy();
      mathGame.text.destroy();
      mathGame.startGame();

    });
    */


  }

  getRandomAssetName() {

    if (Math.floor(Math.random() * 2) === 0) {

      return "girl";

    }
    return "boy";
  }

  shuffle(array) {
    let counter = array.length;

    // While there are elements in the array
    while (counter > 0) {
      // Pick a random index
      let index = Math.floor(Math.random() * counter);

      // Decrease counter by 1
      counter--;

      // And swap the last element with it
      let temp = array[counter];
      array[counter] = array[index];
      array[index] = temp;
    }

    return array;
  }


}
