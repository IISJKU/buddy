let Director = {

  changeScene:function (currentScene){

    game.scene.stop(currentScene);

    console.log(currentScene);
    switch (currentScene) {

      case "Intro": {

        game.scene.start("ReadingGameTTSWord");
        break;
      }

      case "ReadingGameTTSWord": {

        game.scene.start("ReadingGameTTSSentence");
        break;
      }
      case "ReadingGameTTSSentence": {

        game.scene.start("ReadingGameText");
        break;
      }
      case "ReadingGameText": {

        game.scene.start("MathGame");
        break;
      }


      default: {

        break;
      }
    }


  }
}
