class MathGame extends Phaser.Scene {

  constructor() {
    super('MathGame');
    this.text = null;
  }

  preload() {
    this.cameras.main.backgroundColor = Phaser.Display.Color.HexStringToColor("#3498db");
    this.load.image('house', 'modules/buddy_profile_wizard/assets/img/house.png');
    this.load.image('girl', 'modules/buddy_profile_wizard/assets/img/girl.png');
    this.load.image('boy', 'modules/buddy_profile_wizard/assets/img/boy.png');
    this.load.audio('explosion', 'modules/buddy_profile_wizard/assets/sounds/drum.wav');
  }

  create() {

    this.text = this.add.text(
      this.cameras.main.centerX,
      200,
      "Count the people in the house",
      {
        fontSize: 25,
        color: "#000000",
        fontStyle: "bold"
      }).setOrigin(0.5);

    this.text.setInteractive(new Phaser.Geom.Rectangle(0, 0, this.text.width, this.text.height), Phaser.Geom.Rectangle.Contains);


    this.text.on("pointerdown", function (pointer) {

      console.log(pointer);
      explosion.play();

    });


    let explosion = this.sound.add('explosion');
    let sprite = this.add.sprite(this.cameras.main.centerX, -400, "house").setInteractive();
    sprite.setScale(0.5);

    sprite.on("pointerdown", function (pointer) {

      console.log(pointer);
      explosion.play();
      game.scene.stop("MathGame");
      game.scene.start("MemoryGame");
    });


    /*
    let tween = this.tweens.add({
      targets: sprite,
      props: {
        y: { value: this.cameras.main.centerY, duration: 1000, ease: 'Bounce.easeOut'}
      },
      onComplete: function(tween, targets){
        console.log("HI");
      }
    });
    */

   // this.insertPeople(3, 0, 500);
    this.removePeople(3,0,500);
  }

  nextStep() {


    console.log("aaa");

  }

  insertPeople(amount, delay, triggerNextStep = true) {

    let speed = 5;
    let currentX = 0;
    let mathGame = this;
    let persons = [];
    for (let i = 0; i < amount; i++) {
      let distance = (this.cameras.main.centerX - (currentX + i * 100));
      let person = this.add.sprite(currentX + i * 100, this.cameras.main.centerY, "girl");
      person.setDepth(-1);
      persons.push(person);


      let tween = this.tweens.add({
        targets: person,
        props: {
          x: {
            value: this.cameras.main.centerX,
            duration: distance * speed,
          }
        },/*
        onComplete: function(tween, targets){
          console.log("HI");
        }*/
      });

      if (i === 0 && triggerNextStep) {
        tween.addListener("complete", function (tween, targets) {

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

  removePeople(amount, delay, triggerNextStep = true) {
    let speed = 5;
    let currentX = 0;
    let mathGame = this;
    let persons = [];
    for (let i = 0; i < amount; i++) {
      let distance = this.cameras.main.width - this.cameras.main.centerX;
      let person = this.add.sprite(this.cameras.main.centerX, this.cameras.main.centerY, "girl");
      person.setDepth(-1);
      persons.push(person);

      let tween = this.tweens.add({
        targets: person,
        props: {
          x: {
            value: this.cameras.main.width,
            duration: distance * speed,
            delay: i*100 * speed,
          }
        },
      });

      if (i === amount - 1 && triggerNextStep) {
        tween.addListener("complete", function (tween, targets) {

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


}
