let Director = {

  changeScene:function (currentScene){

    game.scene.stop(currentScene);

    switch (currentScene) {

      case "Intro": {

        game.scene.start("ReadingGameTTSWord");
        break;
      }

      case "ReadingGameTTSWord": {

        game.scene.start("ReadingGameTTSSentence");
        break;
      }

      default: {

        break;
      }
    }


  }
}
